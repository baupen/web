<?php

namespace App\Command\Base;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;

abstract class DatabaseCommand extends Command
{
    private ManagerRegistry $registry;

    protected const string BACKUP_FILE_PREFIX = 'mysql_';

    /**
     * DatabaseCommand constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct();

        $this->registry = $registry;
    }

    protected function getDatabaseConfiguration(): array
    {
        /** @var Connection $connection */
        $connection = $this->registry->getConnection();
        $params = $connection->getParams();

        return [
            'host' => $params['host'],
            'database' => $params['dbname'] ?? $params['path'],
            'username' => $params['user'],
            'password' => $params['password'],
        ];
    }

    protected function getMysqlCommandLineConnectionParameters(): string
    {
        $config = $this->getDatabaseConfiguration();

        return '--host=' . $config['host'] . ' --user=' . $config['username'] . ' --password=' . $config['password'] . ' ' . $config['database'];
    }
}
