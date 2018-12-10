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
use App\Entity\MapSector;
use App\Entity\Traits\FileTrait;
use App\Model\Frame;
use App\Model\Point;
use App\Model\SyncTransaction;
use App\Service\Interfaces\DisplayNameServiceInterface;
use App\Service\Interfaces\FileSystemSyncServiceInterface;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class FileSystemSyncService implements FileSystemSyncServiceInterface
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
     * @var DisplayNameServiceInterface
     */
    private $displayNameService;

    public function __construct(RegistryInterface $registry, PathServiceInterface $pathService, ImageServiceInterface $imageService, DisplayNameServiceInterface $displayNameService)
    {
        $this->registry = $registry;
        $this->pathService = $pathService;
        $this->imageService = $imageService;
        $this->displayNameService = $displayNameService;
    }

    /**
     * syncs the filesystem with the database, creating/updating construction sites as needed.
     *
     * @throws \Exception
     */
    public function sync()
    {
        $constructionSites = $this->registry->getRepository(ConstructionSite::class)->findAll();
        /** @var ConstructionSite[] $constructionSitesLookup */
        $constructionSitesLookup = [];
        foreach ($constructionSites as $constructionSite) {
            $constructionSitesLookup[$constructionSite->getFolderName()] = $constructionSite;
        }

        $existingDirectories = glob($this->pathService->getConstructionSiteFolderRoot() . \DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
        foreach ($existingDirectories as $directory) {
            $folderName = mb_substr($directory, mb_strrpos($directory, \DIRECTORY_SEPARATOR) + 1);

            $syncTransaction = new SyncTransaction();
            if (!array_key_exists($folderName, $constructionSitesLookup)) {
                $this->addConstructionSite($syncTransaction, $directory);
            } else {
                $this->syncConstructionSite($syncTransaction, $constructionSitesLookup[$folderName]);
            }

            $this->commitSyncTransaction($syncTransaction);
        }
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param string $directory
     */
    private function addConstructionSite(SyncTransaction $syncTransaction, string $directory)
    {
        $folderName = mb_substr($directory, mb_strrpos($directory, \DIRECTORY_SEPARATOR) + 1);
        $constructionSite = new ConstructionSite();
        $constructionSite->setFolderName($folderName);
        $constructionSite->setName($this->displayNameService->forConstructionSite($folderName));

        $syncTransaction->persist($constructionSite);
        $this->syncConstructionSite($syncTransaction, $constructionSite);
    }

    /**
     * @param string $folder
     * @param string $ending
     * @param FileTrait[] $knownFiles
     * @param callable $createNewFile
     *
     * @return FileTrait[]
     */
    private function getFiles(string $folder, string $ending, array $knownFiles, callable $createNewFile)
    {
        $knownFilesLookup = [];
        foreach ($knownFiles as $knownFile) {
            $knownFilesLookup[$knownFile->getFilename()] = $knownFile;
        }

        /** @var FileTrait[] $newFiles */
        $newFiles = [];

        $folderLength = \mb_strlen($folder);
        $files = glob($folder . \DIRECTORY_SEPARATOR . '*' . $ending);
        foreach ($files as $file) {
            $fileName = mb_substr($file, $folderLength + 1);

            if (!array_key_exists($fileName, $knownFilesLookup)) {
                /** @var FileTrait $fileTrait */
                $fileTrait = $createNewFile($file);
                $fileTrait->setFilename($fileName);
                $fileTrait->setHash(hash_file('sha256', $file));
                $newFiles[] = $fileTrait;
            }
        }

        return $newFiles;
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param ConstructionSite $constructionSite
     * @param Map[] $maps
     *
     * @return Map[]
     */
    private function createTreeStructure(SyncTransaction $syncTransaction, ConstructionSite $constructionSite, array &$maps)
    {
        $mapLookup = [];
        foreach ($maps as $map) {
            $mapLookup[$map->getName()] = $map;
        }
        $mapNames = array_keys($mapLookup);

        // ensure a map exists for all common prefixes (as a folder)
        $prefixMap = $this->createPrefixMap(array_keys($mapLookup));
        foreach ($prefixMap as $prefix => $count) {
            if ($count > 1) {
                if (!array_key_exists($prefix, $mapLookup)) {
                    $map = new Map();
                    $map->setConstructionSite($constructionSite);
                    $map->setName($prefix);
                    $mapLookup[$prefix] = $map;
                    $maps[] = $map;
                    $syncTransaction->persist($map);
                }
            }
        }

        // find longest matching prefix & set as parent
        foreach ($maps as $map) {
            $parts = explode(' ', $map->getName());
            for ($i = \count($parts); $i > 0; --$i) {
                $prefix = '';
                for ($j = 0; $j < $i; ++$j) {
                    $prefix .= $parts[$j] . ' ';
                }

                // find shortest maps name with that prefix
                $shortestPrefixMatch = null;
                foreach ($mapNames as $mapName) {
                    // prefix match
                    if (\mb_strpos($mapName, $prefix) === 0) {
                        if ($shortestPrefixMatch === null || \mb_strlen($mapName) < \mb_strlen($shortestPrefixMatch)) {
                            $shortestPrefixMatch = $mapName;
                        }
                    }
                }

                // if map found, set as parent & stop for current map
                if ($shortestPrefixMatch !== null) {
                    $parent = $mapLookup[$shortestPrefixMatch];
                    if ($parent !== $map) {
                        $map->setParent($parent);
                        $syncTransaction->persist($map);
                    }
                    break;
                }
            }
        }

        return $maps;
    }

    /**
     * counts prefixes occurring and returns a dictionary with (prefix => count).
     *
     * @param string[] $names
     *
     * @return int[]
     */
    private function createPrefixMap(array $names)
    {
        $prefixMap = [];

        foreach ($names as $name) {
            $currentPrefix = $name;

            while (true) {
                if (!array_key_exists($currentPrefix, $prefixMap)) {
                    $prefixMap[$currentPrefix] = 1;
                } else {
                    ++$prefixMap[$currentPrefix];
                }

                $newCutoff = \mb_strripos($currentPrefix, ' ');
                if ($newCutoff === false) {
                    break;
                }

                $currentPrefix = trim(\mb_substr($currentPrefix, 0, $newCutoff));
            }
        }

        return $prefixMap;
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param ConstructionSite $constructionSite
     */
    private function syncConstructionSite(SyncTransaction $syncTransaction, ConstructionSite $constructionSite)
    {
        /**
         * conventions:
         * *.jpgs containing visualizations of the construction site are inside the /images folder
         * pdfs/dwgs containing maps are inside the /maps folder
         * if a pdf/dwg/jpg file should be added, but one already exists with a different hash, the new file is created as "<original_filename>_hash<hash>.<original_extension>
         * for example, if the file "preview.jpg" already exists, a file "preview_hash872z71237q8w78712837.jpg" is added if it does not exist already.
         * no file other than of type json is ever replaced/removed; only add is allowed.
         */
        $constructionSiteImages = $this->registry->getRepository(ConstructionSiteImage::class)->findBy(['constructionSite' => $constructionSite->getId()]);

        $this->findNewConstructionSiteImages($syncTransaction, $constructionSite, $constructionSiteImages);

        $this->refreshConstructionSiteImageFileNames($syncTransaction, $constructionSiteImages);

        $this->chooseMostAppropriateImageForConstructionSite($syncTransaction, $constructionSite, $constructionSiteImages);

        $this->syncConstructionSiteMaps($syncTransaction, $constructionSite);
    }

    /**
     * @param SyncTransaction $transaction
     */
    private function commitSyncTransaction(SyncTransaction $transaction)
    {
        $manager = $this->registry->getManager();

        $cacheInvalidatedEntities = [Map::class => [], MapFile::class => [], ConstructionSite::class => [], ConstructionSiteImage::class => []];

        $transaction->execute(
            $manager,
            function ($entity, $class) use (&$cacheInvalidatedEntities) {
                if (array_key_exists($class, $cacheInvalidatedEntities)) {
                    $cacheInvalidatedEntities[$class][] = $entity;
                }

                return true;
            }
        );
        $manager->flush();

        foreach ($cacheInvalidatedEntities[Map::class] as $cacheInvalidatedEntity) {
            /* @var Map $cacheInvalidatedEntity */
            $this->imageService->warmupCacheForMap($cacheInvalidatedEntity);
        }

        foreach ($cacheInvalidatedEntities[MapFile::class] as $cacheInvalidatedEntity) {
            /* @var MapFile $cacheInvalidatedEntity */
            $this->imageService->warmupCacheForMap($cacheInvalidatedEntity->getMap());
        }

        foreach ($cacheInvalidatedEntities[ConstructionSite::class] as $cacheInvalidatedEntity) {
            /* @var ConstructionSite $cacheInvalidatedEntity */
            $this->imageService->warmupCacheForConstructionSite($cacheInvalidatedEntity);
        }

        foreach ($cacheInvalidatedEntities[ConstructionSiteImage::class] as $cacheInvalidatedEntity) {
            /* @var ConstructionSiteImage $cacheInvalidatedEntity */
            $this->imageService->warmupCacheForConstructionSite($cacheInvalidatedEntity->getConstructionSite());
        }
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param ConstructionSiteImage[] $constructionSiteImages
     */
    private function refreshConstructionSiteImageFileNames(SyncTransaction $syncTransaction, array $constructionSiteImages)
    {
        foreach ($constructionSiteImages as $constructionSiteImage) {
            $newName = $this->displayNameService->forConstructionSiteImage($constructionSiteImage->getFilename());
            if ($newName !== $constructionSiteImage->getDisplayFilename()) {
                $constructionSiteImage->setDisplayFilename($newName);
                $syncTransaction->persist($constructionSiteImage);
            }
        }
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param ConstructionSite $constructionSite
     * @param ConstructionSiteImage[] $constructionSiteImages
     */
    private function chooseMostAppropriateImageForConstructionSite(SyncTransaction $syncTransaction, ConstructionSite $constructionSite, array $constructionSiteImages)
    {
        // refresh current image if needed
        if (!$constructionSite->getPreventAutomaticEdit()) {
            if ($constructionSite->getImage() !== null) {
                foreach ($constructionSiteImages as $possibleMatch) {
                    if ($constructionSite->getImage()->getDisplayFilename() === $possibleMatch->getDisplayFilename() &&
                        ($possibleMatch->getCreatedAt() === null || $possibleMatch->getCreatedAt() > $constructionSite->getImage()->getCreatedAt())) {
                        //replace match & stop
                        $constructionSite->setImage($possibleMatch);
                        $syncTransaction->persist($constructionSite);
                    }
                }
            } elseif (\count($constructionSiteImages) > 0) {
                // set initial image if none
                $newImage = $constructionSiteImages[0];

                $constructionSite->setImage($newImage);
                $syncTransaction->persist($constructionSite);
            }
        }
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param ConstructionSite $constructionSite
     * @param ConstructionSiteImage[] $constructionSiteImages
     */
    private function findNewConstructionSiteImages(SyncTransaction $syncTransaction, ConstructionSite $constructionSite, array &$constructionSiteImages)
    {
        $constructionSiteImagesDirectory = $this->pathService->getFolderForConstructionSiteImage($constructionSite);
        /** @var ConstructionSiteImage[] $newConstructionSiteImages */
        $newConstructionSiteImages = $this->getFiles($constructionSiteImagesDirectory, '.jpg', $constructionSiteImages, function () {
            return new ConstructionSiteImage();
        });

        foreach ($newConstructionSiteImages as $newConstructionSiteImage) {
            $newConstructionSiteImage->setConstructionSite($constructionSite);
            $constructionSite->getImages()->add($newConstructionSiteImage);
            $syncTransaction->persist($newConstructionSiteImage);
            $constructionSiteImages[] = $newConstructionSiteImage;
        }
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param ConstructionSite $constructionSite
     */
    private function syncConstructionSiteMaps(SyncTransaction $syncTransaction, ConstructionSite $constructionSite)
    {
        // get all files from the directory
        /** @var MapFile[] $mapFiles */
        $mapFiles = $this->registry->getRepository(MapFile::class)->findBy(['constructionSite' => $constructionSite->getId()]);
        /** @var Map[] $maps */
        $maps = $this->registry->getRepository(Map::class)->findBy(['constructionSite' => $constructionSite->getId()]);

        $this->findNewMapFiles($syncTransaction, $constructionSite, $mapFiles);

        $this->syncMapFiles($syncTransaction, $constructionSite, $mapFiles);

        $this->refreshMapFileDisplayFileNames($syncTransaction, $mapFiles);

        $this->assignMapFilesToMaps($syncTransaction, $constructionSite, $mapFiles, $maps);

        $this->createTreeStructure($syncTransaction, $constructionSite, $maps);
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param ConstructionSite $constructionSite
     * @param MapFile[] $mapFiles
     */
    private function findNewMapFiles(SyncTransaction $syncTransaction, ConstructionSite $constructionSite, array &$mapFiles)
    {
        $mapsDirectory = $this->pathService->getFolderForMapFile($constructionSite);
        /** @var MapFile[] $newMapFiles */
        $newMapFiles = $this->getFiles($mapsDirectory, '.pdf', $mapFiles, function () {
            return new MapFile();
        });

        foreach ($newMapFiles as $newMapFile) {
            $newMapFile->setConstructionSite($constructionSite);
            $syncTransaction->persist($newMapFile);
            $mapFiles[] = $newMapFile;
        }
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param ConstructionSite $constructionSite
     * @param MapFile[] $mapFiles
     */
    private function syncMapFiles(SyncTransaction $syncTransaction, ConstructionSite $constructionSite, array $mapFiles)
    {
        $directory = $this->pathService->getFolderForMapFile($constructionSite);
        foreach ($mapFiles as $mapFile) {
            $fileNameWithoutExtension = mb_substr($mapFile->getFilename(), 0, -3);

            $mapSectorsJsonPath = $directory . \DIRECTORY_SEPARATOR . $fileNameWithoutExtension . 'sectors.json';
            $mapSectors = $this->readMapSectors($mapSectorsJsonPath);
            $this->syncMapSectors($syncTransaction, $mapFile, $mapSectors);

            $frameJsonPath = $directory . \DIRECTORY_SEPARATOR . $fileNameWithoutExtension . 'sectors.frame.json';
            $frame = $this->readFrame($frameJsonPath);
            $this->syncFrame($syncTransaction, $mapFile, $frame);
        }
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param MapFile $mapFile
     * @param Frame|null $frame
     */
    private function syncFrame(SyncTransaction $syncTransaction, MapFile $mapFile, ?Frame $frame)
    {
        if ($mapFile->getSectorFrame() === null && $frame === null) {
            return;
        }

        if ($mapFile->getSectorFrame() !== null && $frame !== null && !$frame->equals($mapFile->getSectorFrame())) {
            return;
        }

        $mapFile->setSectorFrame($frame);
        $syncTransaction->persist($mapFile);
    }

    /**
     * @param string $filePath
     *
     * @return Frame|null
     */
    private function readFrame(string $filePath)
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $json = json_decode(file_get_contents($filePath));

        if (!property_exists($json, 'upper') ||
            !property_exists($json, 'lower') ||
            !property_exists($json, 'left') ||
            !property_exists($json, 'right')) {
            return null;
        }

        $frame = new Frame();
        $frame->startX = $json->left;
        $frame->startY = $json->right;
        $frame->width = $json->right - $json->left;
        $frame->height = $json->lower - $json->upper;

        return $frame;
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param MapFile $mapFile
     * @param MapSector[] $newMapSectors
     */
    private function syncMapSectors(SyncTransaction $syncTransaction, MapFile $mapFile, array $newMapSectors)
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
            if (!array_key_exists($key, $newSectorsLookup) || !$sector->equals($newSectorsLookup[$key])) {
                $mapFile->getSectors()->remove($sector);
                $syncTransaction->remove($sector);
            } else {
                unset($newSectorsLookup[$key]);
            }
        }

        // add new ones
        foreach ($newMapSectors as $newSector) {
            $newSector->setMapFile($mapFile);
            $mapFile->getSectors()->add($newSector);
            $syncTransaction->persist($newSector);
        }
    }

    /**
     * @param $filePath
     *
     * @return MapSector[]|array
     */
    private function readMapSectors($filePath)
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
     * @param string $name
     *
     * @return string
     */
    private function getColorFromMapSectorName(string $name)
    {
        $name = trim(mb_strtolower($name));

        // remove all other chars containing no information
        $name = preg_replace("/[\s-&]/", '', $name);

        $kitchen = ['küche', 'essen', 'kochen'];
        $wetAreas = ['wc', 'bad', 'klo', 'toilette'];
        $livingArea = ['wohn', 'zimmer', 'essen', 'schlaf', 'gäste', 'gast'];
        $storage = ['keller', 'auto', 'garage', 'wasch', 'wirtschaft'];

        $checkIfMatch = function (array $needles) use ($name) {
            foreach ($needles as $needle) {
                if (\mb_strpos($name, $needle) !== false) {
                    return true;
                }
            }

            return false;
        };

        if ($checkIfMatch($kitchen)) {
            // red because of fire
            return 'FF0000';
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
     * @param \stdClass $pointsJson
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
            $point->y = $item->x;
            $points[] = $point;
        }

        return $points;
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param MapFile[] $mapFiles
     */
    private function refreshMapFileDisplayFileNames(SyncTransaction $syncTransaction, array $mapFiles)
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

    /**
     * @param SyncTransaction $syncTransaction
     * @param ConstructionSite $constructionSite
     * @param MapFile[] $mapFiles
     * @param Map[] $maps
     */
    private function assignMapFilesToMaps(SyncTransaction $syncTransaction, ConstructionSite $constructionSite, array $mapFiles, array &$maps)
    {
        /** @var Map[] $displayNameToMapLookup */
        $displayNameToMapLookup = [];
        foreach ($maps as $map) {
            $key = $map->getName();
            if (!array_key_exists($key, $displayNameToMapLookup)) {
                $displayNameToMapLookup[$key] = $map;
            } elseif ($displayNameToMapLookup[$key]->getPreventAutomaticEdit() && !$map->getPreventAutomaticEdit()) {
                $displayNameToMapLookup[$key] = $map;
            }
        }

        foreach ($mapFiles as $mapFile) {
            if ($mapFile->getMap() !== null) {
                continue;
            }

            $key = $mapFile->getDisplayFilename();
            if (array_key_exists($key, $displayNameToMapLookup)) {
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
}
