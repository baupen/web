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
use App\Entity\Map;
use App\Entity\MapFile;
use App\Model\SyncTransaction;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Sync\Interfaces\DisplayNameServiceInterface;
use App\Service\Sync\Interfaces\FileServiceInterface;
use App\Service\Sync\Interfaces\MapFileServiceInterface;
use App\Service\Sync\Interfaces\MapServiceInterface;

class MapService implements MapServiceInterface
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
     * @var MapFileServiceInterface
     */
    private $mapFileService;

    public function __construct(PathServiceInterface $pathService, DisplayNameServiceInterface $displayNameService, FileServiceInterface $fileService, MapFileServiceInterface $mapFileService)
    {
        $this->pathService = $pathService;
        $this->displayNameService = $displayNameService;
        $this->fileService = $fileService;
        $this->mapFileService = $mapFileService;
    }

    /**
     * @param SyncTransaction  $syncTransaction
     * @param ConstructionSite $constructionSite
     */
    public function syncConstructionSiteMaps(SyncTransaction $syncTransaction, ConstructionSite $constructionSite)
    {
        $this->mapFileService->syncMapFiles($syncTransaction, $constructionSite);

        /** @var Map[] $maps */
        $maps = $constructionSite->getMaps()->toArray();
        /** @var MapFile[] $mapFiles */
        $mapFiles = $constructionSite->getMapFiles()->toArray();

        $this->refreshDisplayName($syncTransaction, $maps);

        $this->assignMapFilesToMaps($syncTransaction, $constructionSite, $mapFiles, $maps);

        $this->createTreeStructure($syncTransaction, $constructionSite, $maps);
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param Map[]           $maps
     */
    private function refreshDisplayName(SyncTransaction $syncTransaction, array $maps)
    {
        foreach ($maps as $map) {
            if ($map->getFile() !== null) {
                $newDisplayName = $map->getFile()->getDisplayFilename();

                if ($newDisplayName !== $map->getName()) {
                    $map->setName($newDisplayName);
                    $syncTransaction->persist($map);
                }
            }
        }
    }

    /**
     * @param SyncTransaction  $syncTransaction
     * @param ConstructionSite $constructionSite
     * @param MapFile[]        $mapFiles
     * @param Map[]            $maps
     */
    private function assignMapFilesToMaps(SyncTransaction $syncTransaction, ConstructionSite $constructionSite, array $mapFiles, array &$maps)
    {
        /** @var Map[] $displayNameToMapLookup */
        $displayNameToMapLookup = [];
        foreach ($maps as $map) {
            $key = $map->getName();
            if (!\array_key_exists($key, $displayNameToMapLookup)) {
                $displayNameToMapLookup[$key] = $map;
            }
        }

        foreach ($mapFiles as $mapFile) {
            if ($mapFile->getMap() !== null || $mapFile->isPersistedInDatabase()) {
                continue;
            }

            $key = $mapFile->getDisplayFilename();
            if (\array_key_exists($key, $displayNameToMapLookup)) {
                $targetMap = $displayNameToMapLookup[$key];

                $mapFile->setMap($targetMap);
                $targetMap->getFiles()->add($mapFile);

                $targetMap->setFile($mapFile);
                $targetMap->setName($key);

                $syncTransaction->persist($targetMap);
            } else {
                // create new map for map file
                $map = new Map();
                $map->setName($key);
                $map->setFile($mapFile);
                $map->setConstructionSite($constructionSite);
                $constructionSite->getMaps()->add($map);

                $mapFile->setMap($map);
                $map->getFiles()->add($mapFile);

                $displayNameToMapLookup[$key] = $map;
                $maps[] = $map;
                $syncTransaction->persist($map);
            }
        }
    }

    /**
     * @param SyncTransaction  $syncTransaction
     * @param ConstructionSite $constructionSite
     * @param Map[]            $maps
     */
    private function createTreeStructure(SyncTransaction $syncTransaction, ConstructionSite $constructionSite, array &$maps)
    {
        $id = 0;
        /** @var Map[] $mapLookup */
        $mapLookup = [];
        $idNameLookup = [];
        foreach ($maps as $map) {
            $idNameLookup[$id] = $map->getName();
            $mapLookup[$id++] = $map;
        }

        $createNewElement = function ($name) use (&$id, &$mapLookup, $syncTransaction, $constructionSite) {
            $map = new Map();
            $map->setName($name);

            $map->setConstructionSite($constructionSite);
            $constructionSite->getMaps()->add($map);

            $syncTransaction->persist($map);
            $mapLookup[$id] = $map;

            return $id++;
        };

        $assignChildToParent = function ($childId, $parentId) use (&$mapLookup, $syncTransaction) {
            $childMap = $mapLookup[$childId];
            $parentMap = $mapLookup[$parentId];

            $childMap->setParent($parentMap);
            $parentMap->getChildren()->add($childMap);

            $syncTransaction->persist($childMap);
        };

        $clearParent = function ($childId) use (&$mapLookup, $syncTransaction) {
            $childMap = $mapLookup[$childId];
            if ($childMap->getParent() !== null) {
                $childMap->getParent()->getChildren()->removeElement($childMap);
                $childMap->setParent(null);

                $syncTransaction->persist($childMap);
            }
        };

        $this->displayNameService->putIntoTreeStructure($idNameLookup, $createNewElement, $assignChildToParent, $clearParent);

        $this->cleanTreeStructure($syncTransaction, $maps);
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param Map[]           $maps
     */
    private function cleanTreeStructure(SyncTransaction $syncTransaction, array &$maps)
    {
        // remove child from parents which are not children anymore
        foreach ($maps as $map) {
            /** @var Map[] $children */
            $children = $map->getChildren()->toArray();
            foreach ($children as $child) {
                if ($child->getParent() !== $map) {
                    $map->getChildren()->removeElement($map);
                }
            }
        }

        // remove maps without children & issues
        $mapCount = \count($maps);
        for ($i = 0; $i < $mapCount; ++$i) {
            $map = $maps[$i];

            // no children; no issues; no files -> we dont need this map
            if ($map->getIssues()->count() === 0 && $map->getFiles()->count() === 0) {
                // if only one children; can remove from hierarchy
                if ($map->getChildren()->count() === 1) {
                    /** @var Map $child */
                    $child = $map->getChildren()->first();
                    $parent = $map->getParent();

                    $child->setParent($parent);
                    $parent->getChildren()->add($child);
                    $syncTransaction->persist($child);

                    $map->getChildren()->removeElement($child);
                    $map->setParent(null);
                }

                // if no children can remove
                if ($map->getChildren()->count() === 0) {
                    if ($map->getParent() !== null) {
                        $map->getParent()->getChildren()->removeElement($map);
                        $map->setParent(null);
                    }

                    unset($maps[$i]);
                    $maps = array_values($maps);
                    --$mapCount;
                    $syncTransaction->remove($map);
                }
            }
        }
    }
}
