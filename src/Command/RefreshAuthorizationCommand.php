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
use App\Service\Interfaces\AuthorizationServiceInterface;
use Doctrine\ORM\ORMException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshAuthorizationCommand extends Command
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var AuthorizationServiceInterface
     */
    private $authorizationService;

    /**
     * ImportLdapUsersCommand constructor.
     */
    public function __construct(RegistryInterface $registry, AuthorizationServiceInterface $authorizationService)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('app:authorization:refresh')
            ->setDescription('Authorizes construction managers contained in the whitelists and denies the others access.')
        ;
    }

    /**
     * @throws ORMException
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->registry->getEntityManager();

        $changes = 0;

        $managers = $this->registry->getRepository(ConstructionManager::class)->findAll();
        foreach ($managers as $manager) {
            $newIsEnabled = $this->authorizationService->checkIfAuthorized($manager);
            if ($newIsEnabled !== $manager->getIsEnabled()) {
                $manager->setIsEnabled($newIsEnabled);
                $entityManager->persist($manager);
                ++$changes;
            }
        }

        $entityManager->flush();
        $output->writeln('persisted ' . $changes . ' changes');

        return 0;
    }
}
