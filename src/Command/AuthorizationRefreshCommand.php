<?php

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
    public function __construct(private readonly ManagerRegistry $registry, private readonly UserServiceInterface $userService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:authorization:refresh')
            ->setDescription('Refreshes authorization; e.g. by comparing with whitelists.')
        ;
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityManager = $this->registry->getManager();

        $constructionManagers = $this->registry->getRepository(ConstructionManager::class)->findAll();
        foreach ($constructionManagers as $constructionManager) {
            $this->userService->refreshAuthorization($constructionManager);
            $entityManager->persist($constructionManager);
        }

        $entityManager->flush();

        $io = new SymfonyStyle($input, $output);
        $io->text('Refreshed authorization of ' . count($constructionManagers) . ' construction managers.');

        return 0;
    }
}
