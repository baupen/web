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

use SensioLabs\Security\SecurityChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportLdapUsersCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('app:sync:ldap')
            ->setDescription('Syncs the LDAP users with the database accounts.')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command detects new files and creates appropriate entries in the database.
EOF
            )
        ;
    }

    /**
     * @see Command
     * @see SecurityChecker
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }
}
