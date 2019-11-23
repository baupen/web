<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Sync;

use App\Entity\ConstructionSite;
use App\Entity\MapFile;
use App\Model\SyncTransaction;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Sync\Interfaces\DisplayNameServiceInterface;
use App\Service\Sync\Interfaces\FileServiceInterface;
use App\Service\Sync\Interfaces\MapFileServiceInterface;
use App\Service\Sync\Interfaces\MapFrameServiceInterface;
use App\Service\Sync\Interfaces\MapSectorServiceInterface;

class MapFileService implements MapFileServiceInterface
{
    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var DisplayNameServiceInterface
     */
    private $displayNameService;

    /**
     * @var FileServiceInterface
     */
    private $fileService;

    /**
     * @var MapSectorServiceInterface
     */
    private $mapSectorService;

    /**
     * @var MapFrameServiceInterface
     */
    private $mapFrameService;

    /**
     * MapFileService constructor.
     */
    public function __construct(PathServiceInterface $pathService, DisplayNameServiceInterface $displayNameService, FileServiceInterface $fileService, MapSectorServiceInterface $mapSectorService, MapFrameServiceInterface $mapFrameService)
    {
        $this->pathService = $pathService;
        $this->displayNameService = $displayNameService;
        $this->fileService = $fileService;
        $this->mapSectorService = $mapSectorService;
        $this->mapFrameService = $mapFrameService;
    }

    public function syncMapFiles(SyncTransaction $syncTransaction, ConstructionSite $constructionSite)
    {
        // get all files from the directory
        /** @var MapFile[] $mapFiles */
        $mapFiles = $constructionSite->getMapFiles()->toArray();

        $this->findNewMapFiles($syncTransaction, $constructionSite, $mapFiles);

        $this->refreshDisplayFileNames($syncTransaction, $mapFiles);

        $this->mapSectorService->syncMapSectors($syncTransaction, $constructionSite);

        $this->mapFrameService->syncMapFrames($syncTransaction, $constructionSite);
    }

    /**
     * @param MapFile[] $mapFiles
     */
    private function findNewMapFiles(SyncTransaction $syncTransaction, ConstructionSite $constructionSite, array &$mapFiles)
    {
        $mapsDirectory = $this->pathService->getFolderForMapFile($constructionSite);
        /** @var MapFile[] $newMapFiles */
        $newMapFiles = $this->fileService->getNewFiles($mapsDirectory, '.pdf', $mapFiles, function () {
            return new MapFile();
        });

        foreach ($newMapFiles as $newMapFile) {
            $newMapFile->setConstructionSite($constructionSite);
            $constructionSite->getMapFiles()->add($newMapFile);
            $syncTransaction->persist($newMapFile);
            $mapFiles[] = $newMapFile;
        }
    }

    /**
     * @param MapFile[] $mapFiles
     */
    private function refreshDisplayFileNames(SyncTransaction $syncTransaction, array $mapFiles)
    {
        $mapNames = [];
        foreach ($mapFiles as $mapFile) {
            $mapNames[] = $this->displayNameService->forMapFile($mapFile->getFilename());
        }
        $mapNames = $this->displayNameService->normalizeMapNames($mapNames);
        $counter = 0;
        foreach ($mapFiles as $mapFile) {
            $newDisplayFilename = $mapNames[$counter++];
            if ($newDisplayFilename !== $mapFile->getDisplayFilename()) {
                $mapFile->setDisplayFilename($newDisplayFilename);
                $syncTransaction->persist($mapFile);
            }
        }
    }
}
