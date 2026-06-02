<?php

namespace App\Command;

use App\Command\Base\DatabaseCommand;
use App\Service\Interfaces\PathServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DatabaseRestoreCommand extends DatabaseCommand
{
    private const int ERROR_NO_BACKUP = 1;

    public function __construct(ManagerRegistry $registry, private readonly PathServiceInterface $pathService)
    {
        parent::__construct($registry);
    }

    protected function configure(): void
    {
        $this
            ->setName('app:database:restore')
            ->setDescription('Uses mysql to restore a mysqldump backup.')
            ->setHelp('Calls mysql and uses the newest backup from the persistent folder.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $backupFolder = $this->pathService->getDatabaseBackupFolder();
        $backups = glob($backupFolder . DIRECTORY_SEPARATOR . self::BACKUP_FILE_PREFIX . '*');
        if (0 === count($backups)) {
            $io->error('No backup found.');

            return self::ERROR_NO_BACKUP;
        }

        $latestBackup = $backups[count($backups) - 1];
        $io->text('Importing ' . $latestBackup);

        exec('mysql ' . $this->getMysqlCommandLineConnectionParameters() . ' < ' . $latestBackup);
        $io->text('Imported.');

        return 0;
    }
}
