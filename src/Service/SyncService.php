<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\ConstructionSite;
use App\Entity\ConstructionSiteImage;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Model\SyncTransaction;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\SyncServiceInterface;
use App\Service\Sync\Interfaces\ConstructionSiteServiceInterface;
use const DIRECTORY_SEPARATOR;
use Exception;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SyncService implements SyncServiceInterface
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var ImageServiceInterface
     */
    private $imageService;

    /**
     * @var ConstructionSiteServiceInterface
     */
    private $constructionSiteService;

    /**
     * FileSystemSyncService constructor.
     *
     * @param RegistryInterface                $registry
     * @param PathServiceInterface             $pathService
     * @param ImageServiceInterface            $imageService
     * @param ConstructionSiteServiceInterface $constructionSiteService
     */
    public function __construct(RegistryInterface $registry, PathServiceInterface $pathService, ImageServiceInterface $imageService, ConstructionSiteServiceInterface $constructionSiteService)
    {
        $this->registry = $registry;
        $this->pathService = $pathService;
        $this->imageService = $imageService;
        $this->constructionSiteService = $constructionSiteService;
    }

    /**
     * syncs the filesystem with the database, creating/updating construction sites as needed.
     *
     * @throws Exception
     */
    public function sync()
    {
        $constructionSites = $this->registry->getRepository(ConstructionSite::class)->findAll();
        /** @var ConstructionSite[] $constructionSitesLookup */
        $constructionSitesLookup = [];
        foreach ($constructionSites as $constructionSite) {
            $constructionSitesLookup[$constructionSite->getFolderName()] = $constructionSite;
        }

        $existingDirectories = glob($this->pathService->getConstructionSiteFolderRoot() . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
        foreach ($existingDirectories as $directory) {
            $folderName = mb_substr($directory, mb_strrpos($directory, DIRECTORY_SEPARATOR) + 1);

            $syncTransaction = new SyncTransaction();
            if (!\array_key_exists($folderName, $constructionSitesLookup)) {
                $this->constructionSiteService->addConstructionSite($syncTransaction, $directory);
            } else {
                $this->constructionSiteService->syncConstructionSite($syncTransaction, $constructionSitesLookup[$folderName]);
            }

            $this->commitSyncTransaction($syncTransaction);
        }
    }

    /**
     * syncs single construction site with the filesystem.
     *
     * @param ConstructionSite $constructionSite
     * @param bool             $skipCacheWarmup
     */
    public function syncConstructionSite(ConstructionSite $constructionSite, bool $skipCacheWarmup = false)
    {
        $syncTransaction = new SyncTransaction();
        $this->constructionSiteService->syncConstructionSite($syncTransaction, $constructionSite);
        $this->commitSyncTransaction($syncTransaction, $skipCacheWarmup);
    }

    /**
     * @param SyncTransaction $transaction
     * @param bool            $skipCacheWarmup
     */
    private function commitSyncTransaction(SyncTransaction $transaction, bool $skipCacheWarmup = false)
    {
        $manager = $this->registry->getManager();

        $cacheInvalidatedEntities = [Map::class => [], MapFile::class => [], ConstructionSite::class => [], ConstructionSiteImage::class => []];

        $transaction->execute(
            $manager,
            function ($entity, $class) use (&$cacheInvalidatedEntities) {
                if (\array_key_exists($class, $cacheInvalidatedEntities)) {
                    $cacheInvalidatedEntities[$class][] = $entity;
                }

                return true;
            }
        );
        $manager->flush();

        if ($skipCacheWarmup) {
            return;
        }

        foreach ($cacheInvalidatedEntities[Map::class] as $cacheInvalidatedEntity) {
            /* @var Map $cacheInvalidatedEntity */
            $this->imageService->warmUpCacheForMap($cacheInvalidatedEntity);
        }

        foreach ($cacheInvalidatedEntities[MapFile::class] as $cacheInvalidatedEntity) {
            /** @var MapFile $cacheInvalidatedEntity */
            if ($cacheInvalidatedEntity->getMap() !== null) {
                $this->imageService->warmUpCacheForMap($cacheInvalidatedEntity->getMap());
            }
        }

        foreach ($cacheInvalidatedEntities[ConstructionSite::class] as $cacheInvalidatedEntity) {
            /* @var ConstructionSite $cacheInvalidatedEntity */
            $this->imageService->warmUpCacheForConstructionSite($cacheInvalidatedEntity);
        }

        foreach ($cacheInvalidatedEntities[ConstructionSiteImage::class] as $cacheInvalidatedEntity) {
            /* @var ConstructionSiteImage $cacheInvalidatedEntity */
            $this->imageService->warmUpCacheForConstructionSite($cacheInvalidatedEntity->getConstructionSite());
        }
    }
}
