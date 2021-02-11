<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Helper\HashHelper;
use App\Service\Interfaces\PathServiceInterface;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateSqliteCommand extends Command
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * MigrateSqliteCommand constructor.
     */
    public function __construct(ManagerRegistry $registry, PathServiceInterface $pathService)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->pathService = $pathService;
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('app:migrate:sqlite')
            ->setDescription('Migrates data from the sqlite database.')
            ->setHelp('Clears the target database and then inserts all data from the sqlite database. Refresh authentication and initialize the cache afterwards.');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        list($sourcePdo, $targetPdo) = $this->getConnections();
        $io->text('Connected to databases');

        $this->clearTarget($targetPdo);
        $io->text('Cleared target database');

        $count = $this->migrateConstructionManagers($sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' construction managers');

        $count = $this->migrateConstructionSites($sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' construction sites');

        $count = $this->migrateConstructionSiteConstructionManagers($sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' construction site <-> construction manager relations');

        $count = $this->migrateConstructionSiteImages($sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' construction images');

        $count = $this->migrateMaps($sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' maps');

        $count = $this->migrateCraftsmen($sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' craftsmen');

        return 0;
    }

    private function clearTarget(PDO $targetPdo)
    {
        $referencesToClear = ['map.parent_id'];
        foreach ($referencesToClear as $reference) {
            list($table, $column) = explode('.', $reference);
            $targetPdo->query("UPDATE $table SET $column = NULL");
        }

        $tablesToClear = [
            'email', 'email_template', 'filter',
            'issue_image', 'map_file',
            'issue',
            'map', 'craftsman',
            'construction_site_image',
            'construction_site_construction_manager',
            'construction_manager', 'construction_site',
        ];

        foreach ($tablesToClear as $table) {
            $targetPdo->query("DELETE FROM $table");
        }
    }

    /**
     * @return PDO[]
     *
     * @throws \Exception
     */
    private function getConnections(): array
    {
        $persistentPath = dirname($this->pathService->getRootFolderOfConstructionSites());
        $expectedSqlitePath = $persistentPath.'/data.sqlite';
        if (!file_exists($expectedSqlitePath)) {
            throw new \Exception('sqlite file not found at '.realpath($expectedSqlitePath));
        }

        $sourcePdo = new PDO('sqlite:'.$expectedSqlitePath);

        /** @var Connection $connection */
        $connection = $this->registry->getConnection();
        $targetPdo = new PDO('mysql:host='.$connection->getHost().';dbname='.$connection->getDatabase(), $connection->getUsername(), $connection->getPassword());

        return [$sourcePdo, $targetPdo];
    }

    private function migrateConstructionManagers(PDO $sourcePdo, PDO $targetPdo): int
    {
        /*
         * drops:
         * registration_date (=created_at)
         * is_trial_account => new auth system; can_associate_self == false
         * is_registration_completed => implicit password === null
         * is_external_account => new auth system; can_associate_self == false
         * active_construction_site_id (dropped)
         */

        $commonFields = [
            'id', 'given_name', 'family_name', 'phone', 'locale',
            'created_at', 'last_changed_at',
            'email', 'password', 'authentication_hash', 'is_enabled',
        ];

        $sourceFields = ['is_registration_completed'];

        $migrateReference = function (array &$constructionManager) {
            $constructionManager['authentication_token'] = HashHelper::getHash();
            $constructionManager['authorization_authority'] = null;
            $constructionManager['is_admin_account'] = 0;
            $constructionManager['can_associate_self'] = 0;

            // simply drop. this loses the information who has logged in before; but does not take the risk someone can not login anymore which could before
            unset($constructionManager['is_registration_completed']);
        };

        return $this->migrateTable($sourcePdo, $targetPdo, 'construction_manager', array_merge($commonFields, $sourceFields), $migrateReference);
    }

    private function migrateConstructionSites(PDO $sourcePdo, PDO $targetPdo): int
    {
        /*
         * drops:
         * is_automatic_edit_enabled (removed functionality)
         */

        $commonFields = [
            'id', 'name', 'folder_name', 'is_trial_construction_site',
            'street_address', 'postal_code', 'locality', 'country',
            'created_at', 'last_changed_at',
        ];

        $migrateReference = function (array &$constructionSite) {
            $constructionSite['deleted_at'] = null;

            if ('Schweiz' === $constructionSite['country']) {
                $constructionSite['country'] = 'CH';
            }
        };

        return $this->migrateTable($sourcePdo, $targetPdo, 'construction_site', $commonFields, $migrateReference);
    }

    private function migrateConstructionSiteConstructionManagers(PDO $sourcePdo, PDO $targetPdo): int
    {
        $commonFields = ['construction_site_id', 'construction_manager_id'];

        return $this->migrateTable($sourcePdo, $targetPdo, 'construction_site_construction_manager', $commonFields);
    }

    private function migrateMaps(PDO $sourcePdo, PDO $targetPdo): int
    {
        /*
         * drops:
         * is_automatic_edit_enabled (removed functionality)
         */

        $commonFields = [
            'id', 'construction_site_id',
            'name',
            'created_at', 'last_changed_at',
        ];

        $migrateReference = function (array &$map) {
            $map['deleted_at'] = null;
        };

        $count = $this->migrateTable($sourcePdo, $targetPdo, 'map', $commonFields, $migrateReference);
        if (0 === $count) {
            return 0;
        }

        // set parent_id in second step to avoid breaking FK
        $sql = 'SELECT parent_id, id FROM map';
        $parentIdTuples = $this->fetchAll($sourcePdo, $sql);

        $insertQuery = $targetPdo->prepare('UPDATE map SET parent_id = ? WHERE id = ?');

        foreach ($parentIdTuples as $parentIdTuple) {
            $insertQuery->execute(array_values($parentIdTuple));
        }

        return $count;
    }

    private function migrateCraftsmen(PDO $sourcePdo, PDO $targetPdo): int
    {
        /*
         * drops:
         * write_authorization_token (removed functionality)
         */

        $commonFields = [
            'id', 'construction_site_id',
            'contact_name', 'company', 'trade', 'email',
            'last_email_sent', 'last_online_visit',
            'email_identifier',
            'created_at', 'last_changed_at',
        ];

        $migrateReference = function (array &$craftsman) {
            $craftsman['last_email_received'] = $craftsman['last_email_sent'];
            unset($craftsman['last_email_sent']);
            $craftsman['last_visit_online'] = $craftsman['last_online_visit'];
            unset($craftsman['last_online_visit']);
            $craftsman['authentication_token'] = $craftsman['email_identifier'];
            unset($craftsman['email_identifier']);
        };

        return $this->migrateTable($sourcePdo, $targetPdo, 'craftsman', $commonFields, $migrateReference);
    }

    private function migrateConstructionSiteImages(PDO $sourcePdo, PDO $targetPdo): int
    {
        return $this->migrateFile($sourcePdo, $targetPdo, 'construction_site_image', 'construction_site', 'image_id');
    }

    private function migrateIssueImages(PDO $sourcePdo, PDO $targetPdo): int
    {
        return $this->migrateFile($sourcePdo, $targetPdo, 'issue_image', 'issue', 'image_id');
    }

    private function migrateMapFiles(PDO $sourcePdo, PDO $targetPdo): int
    {
        return $this->migrateFile($sourcePdo, $targetPdo, 'map_file', 'map', 'file_id');
    }

    private function migrateFile(PDO $sourcePdo, PDO $targetPdo, string $table, string $ownerTable, string $ownerColumn): int
    {
        /**
         * drops: displayName (functionality removed).
         */
        $fields = [
            't.id AS id',
            'o.id AS '.$ownerTable.'_id',
            't.created_at AS created_at', 't.last_changed_at AS last_changed_at',
            't.filename AS filename', 't.hash AS hash',
        ];
        $sql = 'SELECT '.implode(', ', $fields).' FROM '.$ownerTable.' o INNER JOIN '.$table.' t ON t.id = o.'.$ownerColumn;

        return $this->migrate($sourcePdo, $targetPdo, $sql, $table);
    }

    private function migrateTable(PDO $sourcePdo, PDO $targetPdo, string $table, array $sourceFields, callable $migrateReference = null): int
    {
        $sql = 'SELECT '.implode(', ', $sourceFields).' FROM '.$table;

        return $this->migrate($sourcePdo, $targetPdo, $sql, $table, $migrateReference);
    }

    private function migrate(PDO $sourcePdo, PDO $targetPdo, string $sql, string $table, callable $migrateReference = null): int
    {
        $entities = $this->fetchAll($sourcePdo, $sql);

        if (is_callable($migrateReference)) {
            foreach ($entities as &$entity) {
                $migrateReference($entity);
            }
            unset($entity); // need to unset &$entity reference variable
        }

        return $this->insertAll($targetPdo, $table, $entities);
    }

    private function fetchAll(PDO $PDO, string $sql)
    {
        $query = $PDO->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    private function insertAll(PDO $targetPdo, string $table, array $entities): int
    {
        $insertCount = count($entities);
        if (0 === $insertCount) {
            return 0;
        }

        $keys = array_keys($entities[0]);
        $placeHolders = array_fill(0, count($keys), '?');
        $insertQuery = $targetPdo->prepare('INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $placeHolders).')');

        foreach ($entities as $entity) {
            $insertQuery->execute(array_values($entity));
        }

        return $insertCount;
    }
}
