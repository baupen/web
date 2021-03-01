<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command\Base;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;

abstract class DatabaseCommand extends Command
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    protected const BACKUP_FILE_PREFIX = 'mysql_';

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

        /* @noinspection PhpDeprecationInspection */
        return [
            'host' => $connection->getHost(),
            'database' => $connection->getDatabase(),
            'username' => $connection->getUsername(),
            'password' => $connection->getPassword(),
        ];
    }

    protected function getMysqlCommandLineConnectionParameters()
    {
        $config = $this->getDatabaseConfiguration();

        return '--user='.$config['username'].' --password='.$config['password'].' '.$config['database'];
    }
}
