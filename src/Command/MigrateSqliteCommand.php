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

        $count = $this->migrateConstructionManagers($sourcePdo, $targetPdo, $io);
        $io->text('Migrated '.$count.' construction managers');

        $count = $this->migrateConstructionSites($sourcePdo, $targetPdo, $io);
        $io->text('Migrated '.$count.' construction sites');

        return 0;
    }

    /**
     * @throws \Exception
     */
    private function clearTarget(PDO $targetPdo)
    {
        $referencesToClear = ['map.parent_id'];
        foreach ($referencesToClear as $reference) {
            list($table, $column) = explode('.', $reference);
            $targetPdo->query("UPDATE $table SET $column = NULL");
        }

        $tablesToClear = [
            'email', 'email_template', 'filter',
            'issue_image', 'issue', 'map_file', 'map', 'craftsman',
            'construction_site_image', 'construction_site_construction_manager',
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

    private function migrateConstructionManagers(PDO $sourcePdo, PDO $targetPdo, SymfonyStyle $io): int
    {
        /*
         * drops:
         * registration_date (=created_at)
         * is_trial_account => new auth system; can_associate_self == false
         * is_registration_completed => implicit password === null
         * is_external_account => new auth system; can_associate_self == false
         * active_construction_site_id (dropped)
         */

        $commonFields = ['id', 'given_name', 'family_name', 'phone', 'locale',
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

        return $this->migrate($sourcePdo, $targetPdo, $io, 'construction_manager', array_merge($commonFields, $sourceFields), $migrateReference);
    }

    private function migrateConstructionSites(PDO $sourcePdo, PDO $targetPdo, SymfonyStyle $io): int
    {
        /*
         * drops:
         * is_automatic_edit_enabled (removed functionality)
         */

        $commonFields = ['id', 'name', 'folder_name', 'is_trial_construction_site',
            'street_address', 'postal_code', 'locality', 'country',
            'created_at', 'last_changed_at',
        ];

        $migrateReference = function (array &$constructionSite) {
            $constructionSite['deleted_at'] = null;

            if ('Schweiz' === $constructionSite['country']) {
                $constructionSite['country'] = 'CH';
            }
        };

        return $this->migrate($sourcePdo, $targetPdo, $io, 'construction_site', $commonFields, $migrateReference);
    }

    private function migrate(PDO $sourcePdo, PDO $targetPdo, SymfonyStyle $io, string $table, array $sourceFields, callable $migrateReference): int
    {
        $query = $sourcePdo->prepare('SELECT '.implode(', ', $sourceFields).' FROM '.$table);
        $query->execute();
        $entities = $query->fetchAll(PDO::FETCH_ASSOC);

        if (0 === count($entities)) {
            $io->warning($table.' is empty');

            return 0;
        }

        foreach ($entities as &$entity) {
            $migrateReference($entity);
        }
        unset($entity);

        $keys = array_keys($entities[0]);
        $placeHolders = array_fill(0, count($keys), '?');
        $insertQuery = $targetPdo->prepare('INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $placeHolders).')');

        foreach ($entities as $entity) {
            $insertQuery->execute(array_values($entity));
        }

        return count($entities);
    }
}
