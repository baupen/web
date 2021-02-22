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
    private const MODE_UPDATE = 'update';
    private const MODE_INSERT = 'insert';

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var string
     */
    private $authorizationMethod;

    /**
     * MigrateSqliteCommand constructor.
     */
    public function __construct(ManagerRegistry $registry, PathServiceInterface $pathService, string $authorizationMethod)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->pathService = $pathService;
        $this->authorizationMethod = $authorizationMethod;
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
        $io->newLine();

        $count = $this->migrateConstructionSiteImages($io, $sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' construction images');
        $io->newLine();

        $count = $this->migrateConstructionSites($io, $sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' construction sites');
        $io->newLine();

        $count = $this->finalizeConstructionSiteImages($io, $sourcePdo, $targetPdo);
        $io->text('Finalized '.$count.' construction images');
        $io->newLine();

        $count = $this->migrateMapFiles($io, $sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' map files');
        $io->newLine();

        $count = $this->migrateMaps($io, $sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' maps');
        $io->newLine();

        $count = $this->finalizeMapFiles($io, $sourcePdo, $targetPdo);
        $io->text('Finalized '.$count.' map files');
        $io->newLine();

        $count = $this->migrateConstructionManagers($io, $sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' construction managers');
        $io->newLine();

        $count = $this->migrateConstructionSiteConstructionManagers($io, $sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' construction site <-> construction manager relations');
        $io->newLine();

        $count = $this->migrateCraftsmen($io, $sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' craftsmen');
        $io->newLine();

        $count = $this->migrateIssueImages($io, $sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' issue images');
        $io->newLine();

        $count = $this->migrateIssues($io, $sourcePdo, $targetPdo);
        $io->text('Migrated '.$count.' issues');
        $io->newLine();

        $count = $this->finalizeIssueImages($io, $sourcePdo, $targetPdo);
        $io->text('Finalized '.$count.' issue images');
        $io->newLine();

        $this->migrateLocalTimeToUTC($targetPdo);
        $io->text('Migrated from local time to UTC');
        $io->newLine();

        $this->migrateSpecialCases($io, $targetPdo);
        $io->text('Finalized');
        $io->newLine();

        return 0;
    }

    private function clearTarget(PDO $targetPdo)
    {
        $referencesToClear = ['map.parent_id', 'issue_image.created_for_id', 'map_file.created_for_id', 'construction_site_image.created_for_id'];
        foreach ($referencesToClear as $reference) {
            list($table, $column) = explode('.', $reference);
            $targetPdo->query("UPDATE $table SET $column = NULL");
        }

        $tablesToClear = [
            'email', 'email_template', 'filter',
            'issue',
            'map', 'craftsman',
            'construction_site_construction_manager',
            'construction_manager', 'construction_site',
            'map_file', 'issue_image', 'construction_site_image',
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

    private function migrateConstructionManagers(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo): int
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

        return $this->migrateTable($io, $sourcePdo, $targetPdo, 'construction_manager', array_merge($commonFields, $sourceFields), $migrateReference);
    }

    private function migrateConstructionSites(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo): int
    {
        /*
         * drops:
         * is_automatic_edit_enabled (removed functionality)
         */

        $fields = [
            'id', 'image_id',
            'name', 'folder_name', 'is_trial_construction_site',
            'street_address', 'postal_code', 'locality', 'country',
            'created_at', 'last_changed_at',
        ];

        $migrateReference = function (array &$constructionSite) {
            if ('Schweiz' === $constructionSite['country']) {
                $constructionSite['country'] = 'CH';
            }
            $constructionSite['is_hidden'] = $constructionSite['is_trial_construction_site'];
            unset($constructionSite['is_trial_construction_site']);
        };

        return $this->migrateTable($io, $sourcePdo, $targetPdo, 'construction_site', $fields, $migrateReference);
    }

    private function migrateConstructionSiteConstructionManagers(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo): int
    {
        $commonFields = ['construction_site_id', 'construction_manager_id'];

        return $this->migrateTable($io, $sourcePdo, $targetPdo, 'construction_site_construction_manager', $commonFields);
    }

    private function migrateMaps(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo): int
    {
        /*
         * drops:
         * is_automatic_edit_enabled (removed functionality)
         */

        $fields = [
            'id', 'construction_site_id', 'file_id',
            'name',
            'created_at', 'last_changed_at',
        ];

        $count = $this->migrateTable($io, $sourcePdo, $targetPdo, 'map', $fields);

        $sql = 'SELECT parent_id, id FROM map';
        $this->migrate($io, $sourcePdo, $targetPdo, $sql, 'map', self::MODE_UPDATE);

        return $count;
    }

    private function migrateCraftsmen(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo): int
    {
        /*
         * drops:
         * write_authorization_token (removed functionality)
         */

        $fields = [
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

        return $this->migrateTable($io, $sourcePdo, $targetPdo, 'craftsman', $fields, $migrateReference);
    }

    private function migrateIssues(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo): int
    {
        /*
         * drops:
         * uploaded_at (same as created_at)
         */

        $fields = [
            'id', 'map_id', 'craftsman_id', 'image_id',
            'number', 'is_marked', 'was_added_with_client', 'description',
            'registered_at', 'created_at',
            'last_changed_at',
        ];

        $mapFields = [
            'construction_site_id',
        ];

        $renames = [
            'response_limit' => 'deadline',
            'upload_by_id' => 'created_by_id',
            'registration_by_id' => 'registered_by_id',
            'response_by_id' => 'resolved_by_id',
            'responded_at' => 'resolved_at',
            'review_by_id' => 'closed_by_id',
            'reviewed_at' => 'closed_at',
        ];

        $migrateReference = function (array &$issue) use ($renames) {
            foreach ($renames as $source => $target) {
                $issue[$target] = $issue[$source];
                unset($issue[$source]);
            }
        };

        $prefixedFields = [];
        foreach ($fields as $field) {
            $prefixedFields[] = 'i.'.$field.' AS '.$field;
        }
        foreach ($mapFields as $field) {
            $prefixedFields[] = 'm.'.$field.' AS '.$field;
        }
        foreach (array_keys($renames) as $field) {
            $prefixedFields[] = 'i.'.$field.' AS '.$field;
        }

        $selectSql = 'SELECT '.implode(', ', $prefixedFields).' FROM issue i INNER JOIN map m on m.id = i.map_id';

        $sql = 'SELECT m.construction_site_id, MAX(i.number) as max_number FROM issue i INNER JOIN map m on m.id = i.map_id GROUP BY m.construction_site_id';
        $constructionSiteMaxResult = $this->fetchAll($sourcePdo, $sql);
        $maxNumbers = [];
        foreach ($constructionSiteMaxResult as $result) {
            $maxNumbers[$result['construction_site_id']] = $result['max_number'];
        }

        $sql = $selectSql.' WHERE number IS NULL';
        $entities = $this->fetchAll($sourcePdo, $sql);
        if (count($entities) > 0) {
            foreach ($entities as &$entity) {
                $migrateReference($entity);

                $constructionSiteId = $entity['construction_site_id'];
                $nextNumber = max($maxNumbers[$constructionSiteId] + 1, 1);
                $maxNumbers[$constructionSiteId] = $nextNumber;
                $entity['number'] = $nextNumber;
            }
            unset($entity); // need to unset &$entity reference variable

            $io->text('Inserting '.count($entities).' with patched number');
            $this->insertAll($targetPdo, 'issue', $entities);
        }

        $sql = $selectSql.' WHERE number IS NOT NULL';
        $count = $this->migrate($io, $sourcePdo, $targetPdo, $sql, 'issue', self::MODE_INSERT, $migrateReference);

        $sql = 'SELECT issue_id as id, position_x, position_y, position_zoom_scale FROM issue_position';
        $this->migrate($io, $sourcePdo, $targetPdo, $sql, 'issue', self::MODE_UPDATE);

        return $count;
    }

    private function migrateConstructionSiteImages(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo): int
    {
        return $this->migrateFile($io, $sourcePdo, $targetPdo, 'construction_site_image', 'construction_site');
    }

    private function migrateIssueImages(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo): int
    {
        return $this->migrateFile($io, $sourcePdo, $targetPdo, 'issue_image', 'issue');
    }

    private function migrateMapFiles(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo): int
    {
        return $this->migrateFile($io, $sourcePdo, $targetPdo, 'map_file', 'map');
    }

    private function finalizeConstructionSiteImages(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo): int
    {
        return $this->setFileCreatedFor($io, $sourcePdo, $targetPdo, 'construction_site_image', 'construction_site');
    }

    private function finalizeIssueImages(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo): int
    {
        return $this->setFileCreatedFor($io, $sourcePdo, $targetPdo, 'issue_image', 'issue');
    }

    private function finalizeMapFiles(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo): int
    {
        return $this->setFileCreatedFor($io, $sourcePdo, $targetPdo, 'map_file', 'map');
    }

    private function migrateFile(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo, string $table, string $ownerName): int
    {
        /**
         * drops: displayName (functionality removed).
         */
        $fields = [
            'id',
            'created_at', 'last_changed_at',
            'filename', 'hash',
        ];

        $sql = 'SELECT '.implode(', ', $fields).' FROM '.$table.' WHERE '.$ownerName.'_id IN (SELECT id FROM '.$ownerName.')';

        return $this->migrate($io, $sourcePdo, $targetPdo, $sql, $table, self::MODE_INSERT);
    }

    private function setFileCreatedFor(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo, string $table, string $ownerName): int
    {
        $sql = 'SELECT id as id, '.$ownerName.'_id as created_for_id FROM '.$table.' WHERE '.$ownerName.'_id IN (SELECT id FROM '.$ownerName.')';

        return $this->migrate($io, $sourcePdo, $targetPdo, $sql, $table, self::MODE_UPDATE);
    }

    private function migrateTable(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo, string $table, array $sourceFields, callable $migrateReference = null): int
    {
        $sql = 'SELECT '.implode(', ', $sourceFields).' FROM '.$table;

        return $this->migrate($io, $sourcePdo, $targetPdo, $sql, $table, self::MODE_INSERT, $migrateReference);
    }

    private function migrate(SymfonyStyle $io, PDO $sourcePdo, PDO $targetPdo, string $sql, string $table, string $mode, callable $migrateReference = null): int
    {
        $limit = 500;
        $offset = 0;

        $total = $this->count($sourcePdo, $table);
        $multipleBatchesRequired = $total > $limit;
        if ($multipleBatchesRequired) {
            $io->text('Large table with '.$total.' entries. Need '.ceil((float) $total / $limit).' batches.');
        }

        while (true) {
            $batchSql = $sql.' LIMIT '.$limit.' OFFSET '.$offset;

            $entities = $this->fetchAll($sourcePdo, $batchSql);
            if (0 === count($entities)) {
                break;
            }

            if (is_callable($migrateReference)) {
                foreach ($entities as &$entity) {
                    $migrateReference($entity);
                }
                unset($entity); // need to unset &$entity reference variable
            }

            $progressExpression = ($offset + count($entities)).'/'.$total;
            $offset += $limit;

            if (self::MODE_INSERT === $mode) {
                $io->text('Inserting '.$progressExpression);
                $this->insertAll($targetPdo, $table, $entities);
            } elseif (self::MODE_UPDATE === $mode) {
                $io->text('Updating '.$progressExpression);
                $this->updateAllById($targetPdo, $table, $entities);
            } else {
                throw new \Exception('Unknown mode '.$mode);
            }
        }

        return $total;
    }

    private function count(PDO $PDO, string $table): int
    {
        $query = $PDO->prepare('SELECT COUNT(*) FROM '.$table);
        $query->execute();

        return $query->fetch(PDO::FETCH_NUM)[0];
    }

    private function fetchAll(PDO $PDO, string $sql)
    {
        $query = $PDO->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    private function updateAllById(PDO $targetPdo, string $table, array $entities): void
    {
        $updateColumns = [];
        foreach (array_keys($entities[0]) as $column) {
            if ('id' !== $column) {
                $updateColumns[] = $column.' = :'.$column;
            }
        }

        $sql = 'UPDATE '.$table.' SET '.implode(', ', $updateColumns).' WHERE id = :id';
        $updateQuery = $targetPdo->prepare($sql);

        foreach ($entities as $entity) {
            $params = [];
            foreach ($entity as $key => $value) {
                $params[':'.$key] = $value;
            }

            $updateQuery->execute($params);
        }
    }

    private function insertAll(PDO $targetPdo, string $table, array $entities): void
    {
        $keys = array_keys($entities[0]);
        $placeHolders = array_fill(0, count($keys), '?');
        $insertQuery = $targetPdo->prepare('INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $placeHolders).')');

        foreach ($entities as $entity) {
            $insertQuery->execute(array_values($entity));
        }
    }

    private function migrateSpecialCases(SymfonyStyle $io, PDO $targetPdo)
    {
        $queries = [
            "UPDATE construction_site SET deleted_at = last_changed_at WHERE id = 'CE6DB20C-6D00-4563-AB83-F35F4512F951'",
        ];

        $io->text('executing '.count($queries).' queries for special cases.');
        foreach ($queries as $query) {
            $targetPdo->exec($query);
        }
    }

    private function migrateLocalTimeToUTC(PDO $targetPdo)
    {
        $impactedFieldsByTable = [
            'construction_manager' => ['created_at', 'last_changed_at'],
            'construction_site' => ['created_at', 'last_changed_at', 'deleted_at'],
            'construction_site_image' => ['created_at', 'last_changed_at'],
            'craftsman' => ['created_at', 'last_changed_at', 'deleted_at', 'last_email_received', 'last_visit_online'],
            'issue' => ['created_at', 'last_changed_at', 'deleted_at', 'deadline', 'created_at', 'registered_at', 'resolved_at', 'closed_at'],
            'issue_image' => ['created_at', 'last_changed_at'],
            'map' => ['created_at', 'last_changed_at', 'deleted_at'],
            'map_file' => ['created_at', 'last_changed_at'],
        ];
        foreach ($impactedFieldsByTable as $table => $impactedFields) {
            $sets = [];
            foreach ($impactedFields as $impactedField) {
                $sets[] = $impactedField.' = DATE_SUB('.$impactedField.', INTERVAL 1 HOUR)';
            }

            $query = 'UPDATE '.$table.' SET '.implode(', ', $sets);
            $targetPdo->exec($query);
        }
    }
}
