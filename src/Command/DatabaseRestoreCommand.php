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

use App\Command\Base\DatabaseCommand;
use App\Service\Interfaces\PathServiceInterface;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DatabaseRestoreCommand extends DatabaseCommand
{
    private const ERROR_NO_BACKUP = 1;

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
            ->setName('app:database:restore')
            ->setDescription('Uses mysql to restore a mysqldump backup.')
            ->setHelp('Calls mysql and uses the newest backup from the persistent folder.');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $backupFolder = $this->pathService->getDatabaseBackupFolder();
        $backups = glob($backupFolder.DIRECTORY_SEPARATOR.self::BACKUP_FILE_PREFIX.'*');
        if (0 === count($backups)) {
            $io->error('No backup found.');

            return self::ERROR_NO_BACKUP;
        }

        $latestBackup = $backups[count($backups) - 1];
        $io->text('Importing '.$latestBackup);

        /** @var Connection $connection */
        $connection = $this->registry->getConnection();
        exec('mysql --user='.$connection->getUsername().' --password='.$connection->getPassword().' '.$connection->getDatabase().' < '.$latestBackup);
        $io->text('Imported.');

        return 0;
    }
}
