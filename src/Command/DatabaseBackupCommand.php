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
use App\Helper\DateTimeFormatter;
use App\Helper\FileHelper;
use App\Service\Interfaces\PathServiceInterface;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DatabaseBackupCommand extends DatabaseCommand
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
            ->setName('app:database:backup')
            ->setDescription('Uses mysqldump to backup database.')
            ->setHelp('Calls mysqldump and stores the backup in the persistent folder.')
            ->addOption('keep', 'k', InputOption::VALUE_OPTIONAL, 'Backups to keep', 3);
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $backupFolder = $this->pathService->getDatabaseBackupFolder();
        FileHelper::ensureFolderExists($backupFolder);

        $filename = self::BACKUP_FILE_PREFIX.(new \DateTime())->format(DateTimeFormatter::FILESYSTEM_DATE_TIME_FORMAT).'.sql';
        $path = $backupFolder.DIRECTORY_SEPARATOR.$filename;
        $io->text('Dumping database to '.$path.'.');

        /** @var Connection $connection */
        $connection = $this->registry->getConnection();
        exec('mysqldump --user='.$connection->getUsername().' --password='.$connection->getPassword().' '.$connection->getDatabase().' > '.$path);
        $io->text('Dumped database.');

        $backups = glob($backupFolder.DIRECTORY_SEPARATOR.self::BACKUP_FILE_PREFIX.'*');
        $allowedBackups = max(0, $input->getOption('keep'));
        $surplusBackups = count($backups) - $allowedBackups;
        if ($surplusBackups > 0) {
            $io->text('Removing '.$surplusBackups.' surplus backup(s).');

            for ($i = 0; $i < $surplusBackups; ++$i) {
                unlink($backups[$i]);
            }
        }

        return 0;
    }
}
