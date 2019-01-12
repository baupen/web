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

use App\Service\Interfaces\SyncServiceInterface;
use SensioLabs\Security\SecurityChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 06/01/2018
 * Time: 19:47.
 */
class SyncCommand extends Command
{
    /**
     * @var SyncServiceInterface
     */
    private $syncService;

    /**
     * CronJobCommand constructor.
     *
     * @param SyncServiceInterface $syncService
     */
    public function __construct(SyncServiceInterface $syncService)
    {
        $this->syncService = $syncService;
        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('app:sync')
            ->setDescription('Syncs the filesystem with the database.')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command detects new files and creates appropriate entries in the database.
EOF
            );
    }

    /**
     * @see Command
     * @see SecurityChecker
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->syncService->sync();

        return 0;
    }
}
