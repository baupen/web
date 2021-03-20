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

use App\Service\Interfaces\CraftsmanServiceInterface;
use App\Service\Interfaces\UserServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;

class SendWeeklyCommand extends Command
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var CraftsmanServiceInterface
     */
    private $craftsmanService;

    /**
     * ImportLdapUsersCommand constructor.
     */
    public function __construct(ManagerRegistry $registry, UserServiceInterface $authorizationService)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->userService = $authorizationService;
    }
}
