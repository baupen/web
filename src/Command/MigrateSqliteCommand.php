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

        $query = $sourcePdo->prepare('SELECT '.implode(', ', $commonFields).', is_registration_completed FROM construction_manager');
        $query->execute();
        $constructionManagers = $query->fetchAll(PDO::FETCH_ASSOC);

        if (0 === count($constructionManagers)) {
            $io->warning('no construction managers');

            return 0;
        }

        foreach ($constructionManagers as &$constructionManager) {
            $constructionManager['authentication_token'] = HashHelper::getHash();
            $constructionManager['authorization_authority'] = null;
            $constructionManager['is_admin_account'] = 0;
            $constructionManager['can_associate_self'] = 0;

            $constructionManager['password'] = $constructionManager['is_registration_completed'] ? $constructionManager['password'] : null;
            unset($constructionManager['is_registration_completed']);
        }

        $keys = array_keys($constructionManagers[0]);
        $placeHolders = array_fill(0, count($keys), '?');
        $insertQuery = $targetPdo->prepare('INSERT INTO construction_manager ('.implode(', ', $keys).') VALUES ('.implode(', ', $placeHolders).')');

        foreach ($constructionManagers as $constructionManager) {
            $insertQuery->execute(array_values($constructionManager));
        }

        return count($constructionManagers);
    }
}
