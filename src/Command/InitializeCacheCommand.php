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

use App\Entity\ConstructionSiteImage;
use App\Entity\IssueImage;
use App\Entity\MapFile;
use App\Service\Interfaces\CacheServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeCacheCommand extends Command
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var CacheServiceInterface
     */
    private $cacheService;

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
            ->setName('app:cache:initialize')
            ->setDescription('Warms up the cache for all database entries.')
        ;
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $constructionSiteImages = $this->registry->getRepository(ConstructionSiteImage::class)->findAll();
        foreach ($constructionSiteImages as $constructionSiteImage) {
            $this->cacheService->warmUpCacheForConstructionSiteImage($constructionSiteImage);
        }

        $mapFiles = $this->registry->getRepository(MapFile::class)->findAll();
        foreach ($mapFiles as $mapFile) {
            $this->cacheService->warmUpCacheForMapFile($mapFile);
        }

        $issueImages = $this->registry->getRepository(IssueImage::class)->findAll();
        foreach ($issueImages as $issueImage) {
            $this->cacheService->warmUpCacheForIssueImage($issueImage);
        }

        return 0;
    }
}
