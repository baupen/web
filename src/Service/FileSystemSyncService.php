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
use App\Service\Interfaces\DisplayNameServiceInterface;
use App\Service\Interfaces\FileSystemSyncServiceInterface;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use Doctrine\Common\Persistence\ObjectManager;
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
            if (!array_key_exists($folderName, $constructionSitesLookup)) {
                $this->addConstructionSite($directory);
            } else {
                $this->syncConstructionSite($constructionSitesLookup[$folderName]);
            }
        }
    }

    /**
     * @param string $directory
     *
     * @throws \Exception
     */
    private function addConstructionSite(string $directory)
    {
        $folderName = mb_substr($directory, mb_strrpos($directory, \DIRECTORY_SEPARATOR) + 1);
        $constructionSite = new ConstructionSite();
        $constructionSite->setFolderName($folderName);
        $constructionSite->setName($this->displayNameService->forConstructionSite($folderName));

        $this->syncConstructionSite($constructionSite);
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
     * @param ConstructionSite $constructionSite
     * @param Map[] $maps
     *
     * @return Map[]
     */
    private function createTreeStructure(ConstructionSite $constructionSite, array &$maps)
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
                }
            }
        }

        // find longest matching prefix & set as parent
        foreach ($maps as $map) {
            $parts = explode(' ', $map->getFile()->getDisplayFilename());
            for ($i = \count($parts); $i > 0; --$i) {
                $prefix = '';
                for ($j = 0; $j < $i; ++$j) {
                    $prefix .= $parts[$j] . ' ';
                }

                // find shortest maps name with that prefix
                $shortestPrefixMatch = null;
                foreach ($mapNames as $mapName) {
                    // prefix match
                    if (mb_strpos($mapName, $prefix) === 0) {
                        if ($shortestPrefixMatch === null || \mb_strlen($mapName) < $shortestPrefixMatch) {
                            $shortestPrefixMatch = $mapName;
                        }
                    }
                }

                // if map found, set as parent & stop for current map
                if ($shortestPrefixMatch !== null) {
                    $parent = $mapLookup[$shortestPrefixMatch];
                    if (!$map->getPreventAutomaticEdit() && $parent !== $map) {
                        $map->setParent($parent);
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

                $newCutoff = mb_strripos($currentPrefix, ' ');
                if ($newCutoff < 0) {
                    break;
                }

                $currentPrefix = trim(mb_substr($currentPrefix, 0, $newCutoff));
            }
        }

        return $prefixMap;
    }

    /**
     * @param ConstructionSite $constructionSite
     */
    private function syncConstructionSite(ConstructionSite $constructionSite)
    {
        /**
         * conventions:
         * *.jpgs containing visualizations of the construction site are inside the /images folder
         * pdfs/dwgs containing maps are inside the /maps folder
         * if a pdf/dwg/jpg file should be added, but one already exists with a different hash, the new file is created as "<original_filename>_hash<hash>.<original_extension>
         * for example, if the file "preview.jpg" already exists, a file "preview_hash872z71237q8w78712837.jpg" is added if it does not exist already.
         * no file other than of type json is ever replaced/removed; only add is allowed.
         */

        // get all files from the directory
        $constructionSiteImages = $this->registry->getRepository(ConstructionSiteImage::class)->findBy(['constructionSite' => $constructionSite->getId()]);

        $manager = $this->registry->getManager();
        $this->findNewConstructionSiteImages($constructionSite, $constructionSiteImages);

        $this->refreshConstructionSiteImageFileNames($constructionSiteImages);

        /** @var ConstructionSiteImage[] $cacheInvalidatedConstructionSiteImages */
        $cacheInvalidatedConstructionSiteImages = [];
        $this->chooseMostAppropriateImageForConstructionSite($constructionSite, $constructionSiteImages, $cacheInvalidatedConstructionSiteImages);

        /** @var Map[] $cacheInvalidatedMaps */
        $cacheInvalidatedMaps = [];
        $this->syncConstructionSiteMaps($constructionSite, $cacheInvalidatedMaps);

        $manager->persist($constructionSite);
        $manager->flush();

        // warmup cache
        $this->warmupCache($cacheInvalidatedConstructionSiteImages, $cacheInvalidatedMaps);
    }

    /**
     * @param ConstructionSiteImage[] $cacheInvalidatedConstructionSiteImages
     * @param Map[] $cacheInvalidatedMaps
     */
    private function warmupCache(array $cacheInvalidatedConstructionSiteImages, array $cacheInvalidatedMaps)
    {
        foreach ($cacheInvalidatedConstructionSiteImages as $cacheInvalidatedConstructionSiteImage) {
            $this->imageService->warmupCacheForConstructionSite($cacheInvalidatedConstructionSiteImage->getConstructionSite());
        }

        foreach ($cacheInvalidatedMaps as $changedMap) {
            $this->imageService->warmupCacheForMap($changedMap);
        }
    }

    /**
     * @param ConstructionSiteImage[] $constructionSiteImages
     */
    private function refreshConstructionSiteImageFileNames(array $constructionSiteImages)
    {
        foreach ($constructionSiteImages as $constructionSiteImage) {
            $constructionSiteImage->setDisplayFilename($this->displayNameService->forConstructionSiteImage($constructionSiteImage->getFilename()));
        }
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param ConstructionSiteImage[] $constructionSiteImages
     * @param ConstructionSiteImage[] $cacheInvalidatedConstructionSiteImages
     */
    private function chooseMostAppropriateImageForConstructionSite(ConstructionSite $constructionSite, array $constructionSiteImages, array &$cacheInvalidatedConstructionSiteImages)
    {
        // refresh current image if needed
        if (!$constructionSite->getPreventAutomaticEdit()) {
            if ($constructionSite->getImage() !== null) {
                $currentName = $constructionSite->getImage()->getDisplayFilename();
                $currentCreatedAt = $constructionSite->getImage()->getCreatedAt();
                foreach ($constructionSiteImages as $possibleMatch) {
                    if ($currentName === $possibleMatch->getDisplayFilename() && $possibleMatch->getCreatedAt() === null || $possibleMatch->getCreatedAt() > $currentCreatedAt) {
                        //replace match & stop
                        $constructionSite->setImage($possibleMatch);
                        $cacheInvalidatedConstructionSiteImages[] = $possibleMatch;

                        return;
                    }
                }
            } elseif (\count($constructionSiteImages) > 0) {
                // set initial image if none
                $newImage = $constructionSiteImages[0];
                $constructionSite->setImage($newImage);
                $cacheInvalidatedConstructionSiteImages[] = $newImage;
            }
        }
    }

    /**
     * @param ObjectManager $manager
     * @param ConstructionSite $constructionSite
     * @param ConstructionSiteImage[] $constructionSiteImages
     */
    private function findNewConstructionSiteImages(ObjectManager $manager, ConstructionSite $constructionSite, array &$constructionSiteImages)
    {
        $constructionSiteImagesDirectory = $this->pathService->getFolderForConstructionSiteImage($constructionSite);
        /** @var ConstructionSiteImage[] $newConstructionSiteImages */
        $newConstructionSiteImages = $this->getFiles($constructionSiteImagesDirectory, '.jpg', $constructionSiteImages, function () {
            return new ConstructionSiteImage();
        });

        foreach ($newConstructionSiteImages as $newConstructionSiteImage) {
            $newConstructionSiteImage->setConstructionSite($constructionSite);
            $constructionSite->getImages()->add($newConstructionSiteImage);
            $manager->persist($newConstructionSiteImage);
            $constructionSiteImages[] = $newConstructionSiteImage;
        }
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param Map[] $cacheInvalidatedMaps
     */
    private function syncConstructionSiteMaps(ObjectManager $manager, ConstructionSite $constructionSite, array &$cacheInvalidatedMaps)
    {
        // get all files from the directory
        /** @var MapFile[] $mapFiles */
        $mapFiles = $this->registry->getRepository(MapFile::class)->findBy(['constructionSite' => $constructionSite->getId()]);
        /** @var Map[] $maps */
        $maps = $this->registry->getRepository(Map::class)->findBy(['constructionSite' => $constructionSite->getId()]);

        //todo: sync mapSectors & points
        $this->findNewMapFiles($manager, $constructionSite, $mapFiles);

        $this->syncMapFiles($manager, $constructionSite, $mapFiles);

        // refresh display file name
        $this->refreshMapFileDisplayFileNames($mapFiles);

        // sets map parent for all newly found map files
        $this->assignMapFilesToMaps($constructionSite, $mapFiles, $maps, $cacheInvalidatedMaps);

        // put all in tree structure
        $this->createTreeStructure($constructionSite, $allMapFiles);
    }

    /**
     * @param ObjectManager $manager
     * @param ConstructionSite $constructionSite
     * @param MapFile[] $mapFiles
     */
    private function findNewMapFiles(ObjectManager $manager, ConstructionSite $constructionSite, array &$mapFiles)
    {
        $mapsDirectory = $this->pathService->getFolderForMapFile($constructionSite);
        /** @var MapFile[] $newMapFiles */
        $newMapFiles = $this->getFiles($mapsDirectory, '.pdf', $mapFiles, function () {
            return new MapFile();
        });

        foreach ($newMapFiles as $newMapFile) {
            $newMapFile->setConstructionSite($constructionSite);
            $manager->persist($newMapFile);
            $mapFiles[] = $newMapFile;
        }
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param MapFile[] $mapFiles
     */
    private function syncMapFiles(ObjectManager $manager, ConstructionSite $constructionSite, array $mapFiles)
    {
        $directory = $this->pathService->getFolderForMapFile($constructionSite);
        foreach ($mapFiles as $mapFile) {
            $fileNameWithoutExtension = mb_substr($mapFile->getFilename(), 0, -3);

            $mapSectorsJsonPath = $directory . \DIRECTORY_SEPARATOR . $fileNameWithoutExtension . 'sectors.json';
            $mapSectors = $this->readMapSectors($mapSectorsJsonPath);
            $this->syncMapSectors($manager, $mapFile, $mapSectors);

            $frameJsonPath = $directory . \DIRECTORY_SEPARATOR . $fileNameWithoutExtension . 'sectors.frame.json';
            $frame = $this->readFrame($frameJsonPath);
            $this->syncFrame($manager, $mapFile, $frame);
        }
    }

    /**
     * @param ObjectManager $manager
     * @param MapFile $mapFile
     * @param Frame|null $frame
     */
    private function syncFrame(ObjectManager $manager, MapFile $mapFile, ?Frame $frame)
    {
        if ($mapFile->getSectorFrame() === null && $frame === null) {
            return;
        }

        if ($mapFile->getSectorFrame() !== null && $frame !== null && !$frame->equals($mapFile->getSectorFrame())) {
            return;
        }

        $mapFile->setSectorFrame($frame);
        $manager->persist($mapFile);
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

        if (!property_exists($json, 'startX') ||
            !property_exists($json, 'startY') ||
            !property_exists($json, 'width') ||
            !property_exists($json, 'height')) {
            return null;
        }

        $frame = new Frame();
        $frame->startX = $json->startX;
        $frame->startY = $json->startY;
        $frame->width = $json->width;
        $frame->height = $json->height;

        return $frame;
    }

    /**
     * @param ObjectManager $manager
     * @param MapFile $mapFile
     * @param MapSector[] $newMapSectors
     */
    private function syncMapSectors(ObjectManager $manager, MapFile $mapFile, array $newMapSectors)
    {
        $existingSectors = $mapFile->getSectors()->toArray();
        if (!$this->checkMapSectorsEqual($existingSectors, $newMapSectors)) {
            foreach ($mapFile->getSectors() as $sector) {
                $mapFile->getSectors()->remove($sector);
                $manager->remove($sector);
            }

            foreach ($newMapSectors as $newMapSector) {
                $newMapSector->setMapFile($mapFile);
                $mapFile->getSectors()->add($newMapSector);
                $manager->persist($newMapSector);
            }
        }
    }

    /**
     * @param MapSector[] $mapSectors1
     * @param MapSector[] $mapSectors2
     *
     * @return bool
     */
    private function checkMapSectorsEqual(array $mapSectors1, array $mapSectors2)
    {
        $mapSectors1Count = \count($mapSectors1);
        if ($mapSectors1Count !== \count($mapSectors2)) {
            return false;
        }

        // simple detection; no fancy out-of-order detection implemented
        for ($i = 0; $i < $mapSectors1Count; ++$i) {
            if (!$mapSectors1[$i]->equals($mapSectors2[$i])) {
                return false;
            }
        }

        return true;
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

        if (!\is_array($json)) {
            return [];
        }

        /** @var MapSector[] $mapSectors */
        $mapSectors = [];
        foreach ($json as $item) {
            if (!property_exists($item, 'name') || !property_exists($item, 'points') || !\is_array($item->points)) {
                continue;
            }

            $mapSector = new MapSector();
            $mapSector->setName($item->name);
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

        $wetAreas = ['wc', 'bad'];
        $livingArea = ['wohn', 'zimmer'];
        $kitchen = ['küche', 'essen'];

        $checkIfMatch = function (array $needles) use ($name) {
            foreach ($needles as $needle) {
                if (mb_strpos($name, $needle) !== -1) {
                    return true;
                }
            }

            return false;
        };

        if ($checkIfMatch($wetAreas)) {
            // blue because of water
            return '#4F628E';
        }

        if ($checkIfMatch($livingArea)) {
            // green because of life
            return '#55AA55';
        }

        if ($checkIfMatch($kitchen)) {
            // red because of fire
            return 'D46A6A';
        }

        // default to grey
        return '#EFEFEF';
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
            $point->x = $item;
            $point->y = $item;
            $points[] = $point;
        }

        return $points;
    }

    /**
     * @param MapFile[] $mapFiles
     */
    private function refreshMapFileDisplayFileNames(array $mapFiles)
    {
        $mapNames = [];
        foreach ($mapFiles as $mapFile) {
            $mapFile->setDisplayFilename($this->displayNameService->forMapFile($mapFile->getFilename()));
            $mapNames[] = $mapFile->getDisplayFilename();
        }
        $mapNames = $this->displayNameService->normalizeMapNames($mapNames);
        $counter = 0;
        foreach ($mapFiles as $mapFile) {
            $mapFile->setDisplayFilename($mapNames[$counter++]);
        }
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param MapFile[] $mapFiles
     * @param Map[] $maps
     * @param Map[] $cacheInvalidatedMaps
     */
    private function assignMapFilesToMaps(ConstructionSite $constructionSite, array $mapFiles, array &$maps, array &$cacheInvalidatedMaps)
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

                // write if not disabled
                if (!$targetMap->getPreventAutomaticEdit()) {
                    $targetMap->setFile($mapFile);
                    $targetMap->setName($key);

                    if (!\in_array($targetMap, $cacheInvalidatedMaps, true)) {
                        $cacheInvalidatedMaps[] = $targetMap;
                    }
                }
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
                $cacheInvalidatedMaps[] = $map;
                $maps[] = $map;
            }
        }
    }
}
