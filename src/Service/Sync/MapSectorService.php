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
use App\Entity\MapSector;
use App\Model\Point;
use App\Model\SyncTransaction;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Sync\Interfaces\MapSectorServiceInterface;
use const DIRECTORY_SEPARATOR;
use stdClass;

class MapSectorService implements MapSectorServiceInterface
{
    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * MapSectorService constructor.
     *
     * @param PathServiceInterface $pathService
     */
    public function __construct(PathServiceInterface $pathService)
    {
        $this->pathService = $pathService;
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param ConstructionSite $constructionSite
     */
    public function syncMapSectors(SyncTransaction $syncTransaction, ConstructionSite $constructionSite)
    {
        /** @var MapFile[] $mapFiles */
        $mapFiles = $constructionSite->getMapFiles()->toArray();

        $directory = $this->pathService->getFolderForMapFile($constructionSite);
        foreach ($mapFiles as $mapFile) {
            $fileNameWithoutExtension = mb_substr($mapFile->getFilename(), 0, -3);

            $mapSectorsJsonPath = $directory . DIRECTORY_SEPARATOR . $fileNameWithoutExtension . 'sectors.json';
            $mapSectors = $this->parseMapSectors($mapSectorsJsonPath);

            $this->applyMapSectors($syncTransaction, $mapFile, $mapSectors);
        }
    }

    /**
     * @param string $filePath
     *
     * @return MapSector[]|array
     */
    private function parseMapSectors(string $filePath)
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $json = json_decode(file_get_contents($filePath));
        if (!property_exists($json, 'floor') || !\is_array($json->floor)) {
            return [];
        }

        /** @var MapSector[] $mapSectors */
        $mapSectors = [];
        foreach ($json->floor as $item) {
            if (!property_exists($item, 'name') || !property_exists($item, 'points') || !property_exists($item, 'identifier') || !\is_array($item->points)) {
                continue;
            }

            $mapSector = new MapSector();
            $cleanChars = '\t\n\r\0\x0B\'"';
            $mapSector->setIdentifier(trim($item->identifier, $cleanChars));
            $mapSector->setName(trim($item->name, $cleanChars));
            $mapSector->setColor($this->getColorFromMapSectorName($mapSector->getName()));
            $mapSector->setPoints($this->getPoints($item->points));
            $mapSectors[] = $mapSector;
        }

        return $mapSectors;
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param MapFile $mapFile
     * @param MapSector[] $newMapSectors
     */
    private function applyMapSectors(SyncTransaction $syncTransaction, MapFile $mapFile, array $newMapSectors)
    {
        /** @var MapSector[] $existingSectorsLookup */
        $existingSectorsLookup = [];
        foreach ($mapFile->getSectors() as $sector) {
            $existingSectorsLookup[$sector->getIdentifier()] = $sector;
        }

        /** @var MapSector[] $newSectorsLookup */
        $newSectorsLookup = [];
        foreach ($newMapSectors as $newMapSector) {
            $newSectorsLookup[$newMapSector->getIdentifier()] = $newMapSector;
        }

        // remove replaced / not found ones
        foreach ($existingSectorsLookup as $key => $sector) {
            if (!\array_key_exists($key, $newSectorsLookup) || !$sector->equals($newSectorsLookup[$key])) {
                $mapFile->getSectors()->removeElement($sector);
                $syncTransaction->remove($sector);
            } else {
                unset($newSectorsLookup[$key]);
            }
        }

        // save changes
        foreach ($newSectorsLookup as $newSector) {
            $newSector->setMapFile($mapFile);
            $mapFile->getSectors()->add($newSector);
            $syncTransaction->persist($newSector);
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getColorFromMapSectorName(string $name)
    {
        $name = trim(mb_strtolower($name));

        // remove all other chars containing no information
        $name = preg_replace("/[\s\-&]/", '', $name);

        $kitchen = ['küche', 'essen', 'kochen'];
        $wetAreas = ['wc', 'bad', 'klo', 'toilette', 'dusch'];
        $livingArea = ['wohn', 'zimmer', 'essen', 'schlaf', 'gäste', 'gast'];
        $storage = ['keller', 'auto', 'garage', 'wasch', 'wirtschaft'];

        $checkIfMatch = function (array $needles) use ($name) {
            foreach ($needles as $needle) {
                if (mb_strpos($name, $needle) !== false) {
                    return true;
                }
            }

            return false;
        };

        if ($checkIfMatch($kitchen)) {
            // red because of fire
            return '#FF0000';
        }

        if ($checkIfMatch($wetAreas)) {
            // blue because of water
            return '#0040FF';
        }

        if ($checkIfMatch($livingArea)) {
            // green because of nature
            return '#00FFFF';
        }

        if ($checkIfMatch($storage)) {
            // grey because its dark there
            return '#808080';
        }

        // default to black
        return '#000000';
    }

    /**
     * @param stdClass[] $pointsJson
     *
     * @return Point[]|array
     */
    private function getPoints($pointsJson)
    {
        /** @var Point[] $points */
        $points = [];
        foreach ($pointsJson as $item) {
            if (!property_exists($item, 'x') || !property_exists($item, 'y')) {
                continue;
            }

            $point = new Point();
            $point->x = $item->x;
            $point->y = $item->y;
            $points[] = $point;
        }

        return $points;
    }
}
