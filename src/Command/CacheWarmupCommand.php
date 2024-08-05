<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\ConstructionSiteImage;
use App\Entity\IssueImage;
use App\Entity\MapFile;
use App\Service\Interfaces\CacheServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CacheWarmupCommand extends Command
{
    private ManagerRegistry $registry;

    private CacheServiceInterface $cacheService;

    /**
     * ImportLdapUsersCommand constructor.
     */
    public function __construct(ManagerRegistry $registry, CacheServiceInterface $cacheService)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->cacheService = $cacheService;
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('app:cache:warmup')
            ->setDescription('Warms up the cache for all database entries.');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $symfony = new SymfonyStyle($input, $output);

        $constructionSiteImages = $this->registry->getRepository(ConstructionSiteImage::class)->findAll();
        $this->warmUpCacheFor($symfony, 'construction site images', $constructionSiteImages, function (ConstructionSiteImage $constructionSiteImage): void {
            $this->cacheService->warmUpCacheForConstructionSiteImage($constructionSiteImage);
        });

        $mapFiles = $this->registry->getRepository(MapFile::class)->findAll();
        $this->warmUpCacheFor($symfony, 'map files', $mapFiles, function (MapFile $mapFile): void {
            $this->cacheService->warmUpCacheForMapFile($mapFile);
        });

        $issueImages = $this->registry->getRepository(IssueImage::class)->findAll();
        $this->warmUpCacheFor($symfony, 'issue images', $issueImages, function (IssueImage $issueImage): void {
            $this->cacheService->warmUpCacheForIssueImage($issueImage);
        });

        return 0;
    }

    private function warmUpCacheFor(SymfonyStyle $io, string $entityPlural, array $entities, callable $actionPerEntity): void
    {
        $constructionSiteImageCount = count($entities);

        $io->text('Warming up cache for '.$constructionSiteImageCount.' '.$entityPlural);
        $io->progressStart($constructionSiteImageCount);
        foreach ($entities as $entity) {
            $actionPerEntity($entity);
            $io->progressAdvance();
        }
        $io->progressFinish();
    }
}
