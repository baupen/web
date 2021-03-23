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

use App\Entity\ConstructionManager;
use App\Service\Interfaces\UserServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AuthorizationRefreshCommand extends Command
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var UserServiceInterface
     */
    private $userService;

    /**
     * ImportLdapUsersCommand constructor.
     */
    public function __construct(ManagerRegistry $registry, UserServiceInterface $authorizationService)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->userService = $authorizationService;
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('app:authorization:refresh')
            ->setDescription('Refreshes authorization; e.g. by comparing with whitelists.')
        ;
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->registry->getManager();

        $constructionManagers = $this->registry->getRepository(ConstructionManager::class)->findAll();
        foreach ($constructionManagers as $constructionManager) {
            $this->userService->refreshAuthorization($constructionManager);
            $entityManager->persist($constructionManager);
        }

        $entityManager->flush();

        $io = new SymfonyStyle($input, $output);
        $io->text('Refreshed authorization of '.count($constructionManagers).' construction managers.');

        return 0;
    }
}
