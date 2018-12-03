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
use App\Entity\Traits\FileTrait;
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

    public function __construct(RegistryInterface $registry, PathServiceInterface $pathService, ImageServiceInterface $imageService)
    {
        $this->registry = $registry;
        $this->pathService = $pathService;
        $this->imageService = $imageService;
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
                $this->syncDirectory($directory, $constructionSitesLookup[$folderName]);
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
        $manager = $this->registry->getManager();

        $folderName = mb_substr($directory, mb_strrpos($directory, \DIRECTORY_SEPARATOR) + 1);
        $constructionSite = new ConstructionSite();
        $constructionSite->setFolderName($folderName);
        $constructionSite->setName($this->deriveNameForConstructionSite($folderName));
        $manager->persist($constructionSite);
        $manager->flush();

        $this->syncDirectory($directory, $constructionSite);
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
     * @param string $directory
     * @param ConstructionSite $constructionSite
     *
     * @throws \Exception
     */
    private function syncDirectory(string $directory, ConstructionSite $constructionSite)
    {
        /**
         * conventions:
         * images for the construction site are inside the /images folder
         * pdfs/dwgs containing maps are inside the /maps folder
         * no file is ever replaced/removed; only add is allowed
         * if a file should be added, but one already exists with a different hash, the new file is created as "<original_filename>_hash<hash>.<original_extension>
         * for example, if the file "preview.jpg" already exists, a file "preview_hash872z71237q8w78712837.jpg" is added if it does not exist already.
         */
        $manager = $this->registry->getManager();

        // get all files from the directory
        $existingFiles = $constructionSite->getImages()->toArray();
        $constructionSiteImagesDirectory = $directory . \DIRECTORY_SEPARATOR . 'images';
        /** @var ConstructionSiteImage[] $newImages */
        $newImages = $this->getFiles($constructionSiteImagesDirectory, '.jpg', $existingFiles, function () {
            return new ConstructionSiteImage();
        });

        /** @var ConstructionSiteImage[] $allImages */
        $allImages = array_merge($newImages, $existingFiles);

        // refresh recommended filenames
        foreach ($allImages as $image) {
            $image->setDisplayFilename($this->deriveNameForConstructionSiteImage($image->getFilename()));
        }

        // add all newly discovered files
        foreach ($newImages as $newImage) {
            $newImage->setConstructionSite($constructionSite);
            $constructionSite->getImages()->add($newImage);
        }

        // refresh current image if needed
        $isCacheInvalidatedConstructionSite = false;
        if (!$constructionSite->getPreventAutomaticEdit()) {
            if ($constructionSite->getImage() !== null) {
                $currentName = $constructionSite->getImage()->getDisplayFilename();
                foreach ($newImages as $newImage) {
                    if ($currentName === $newImage->getDisplayFilename()) {
                        //replace match & stop
                        $constructionSite->setImage($newImage);
                        $isCacheInvalidatedConstructionSite = true;
                        break;
                    }
                }
            } elseif (\count($newImages) > 0) {
                // set initial image if none
                $constructionSite->setImage($newImages[0]);
                $isCacheInvalidatedConstructionSite = true;
            }
        }

        // get all files from the directory
        /** @var MapFile[] $existingMapFiles */
        $existingMapFiles = [];
        foreach ($constructionSite->getMaps() as $map) {
            $existingMapFiles = array_merge($existingMapFiles, $map->getFiles()->toArray());
            foreach ($map->getFiles() as $file) {
                $existingMapFiles[$file->getId()] = $file;
            }
        }
        $mapsDirectory = $directory . \DIRECTORY_SEPARATOR . 'maps';
        /** @var MapFile[] $newMapFiles */
        $newMapFiles = $this->getFiles($mapsDirectory, '.pdf', $existingMapFiles, function () {
            return new MapFile();
        });

        /** @var MapFile[] $allMapFiles */
        $allMapFiles = array_merge($newMapFiles, $existingMapFiles);

        // refresh recommended filenames
        $mapNames = [];
        foreach ($allMapFiles as $mapFile) {
            $mapFile->setDisplayFilename($this->deriveNameForMapFile($mapFile->getFilename()));
            $mapNames[] = $mapFile->getDisplayFilename();
        }
        $mapNames = $this->normalizeNames($mapNames);
        $counter = 0;
        foreach ($allMapFiles as $mapFile) {
            $mapFile->setDisplayFilename($mapNames[$counter++]);
        }

        // create lookup of known file names
        /** @var Map[] $displayNameToMapLookup */
        $displayNameToMapLookup = [];
        foreach ($existingMapFiles as $existingMapFile) {
            $key = $existingMapFile->getDisplayFilename();
            if (!array_key_exists($key, $displayNameToMapLookup)) {
                $displayNameToMapLookup[$key] = $existingMapFile->getMap();
            } elseif ($displayNameToMapLookup[$key]->getPreventAutomaticEdit() && !$existingMapFile->getMap()->getPreventAutomaticEdit()) {
                $displayNameToMapLookup[$key] = $existingMapFile->getMap();
            }
        }

        // tries to find parent for all newly found maps
        /** @var Map[] $cacheInvalidatedMaps */
        $cacheInvalidatedMaps = [];
        foreach ($newMapFiles as $newMapFile) {
            $key = $newMapFile->getDisplayFilename();
            if (array_key_exists($key, $displayNameToMapLookup)) {
                $targetMap = $displayNameToMapLookup[$key];
                $newMapFile->setMap($targetMap);
                $targetMap->getFiles()->add($newMapFile);

                // write if not disabled
                if (!$targetMap->getPreventAutomaticEdit()) {
                    $targetMap->setFile($newMapFile);
                    $targetMap->setName($key);

                    if (!\in_array($targetMap, $cacheInvalidatedMaps, true)) {
                        $cacheInvalidatedMaps[] = $targetMap;
                    }
                }
            } else {
                // create new map for map file
                $map = new Map();
                $map->setName($key);
                $map->setFile($newMapFile);
                $map->setConstructionSite($constructionSite);
                $constructionSite->getMaps()->add($map);
                $map->getFiles()->add($newMapFile);
                $newMapFile->setMap($map);

                $displayNameToMapLookup[$key] = $map;
                $cacheInvalidatedMaps[] = $map;
            }
        }

        // put all in tree structure
        $this->createTreeStructure($constructionSite->getMaps()->toArray());

        $manager->persist($constructionSite);
        $manager->flush();

        // warmup cache
        if ($isCacheInvalidatedConstructionSite) {
            $this->imageService->warmupCacheForConstructionSite($constructionSite);
        }
        foreach ($cacheInvalidatedMaps as $changedMap) {
            $this->imageService->warmupCacheForMap($changedMap);
        }
    }

    /**
     * @param Map[] $maps
     */
    private function createTreeStructure(array $maps)
    {
        $mapLookup = [];
        foreach ($maps as $map) {
            $mapLookup[$map->getName()] = $map;
        }
        $mapNames = array_keys($mapLookup);

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
    }

    /**
     * @param string $folderName
     *
     * @return string
     */
    private function deriveNameForConstructionSite(string $folderName)
    {
        return str_replace('_', ' ', $folderName);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private function deriveNameForFile(string $fileName)
    {
        // strip file ending
        $ending = pathinfo($fileName, PATHINFO_EXTENSION);

        // strip artificial hash
        if (preg_match('/_hash([A-Fa-f0-9]){64}.' . $ending . '/', $fileName, $matches, PREG_OFFSET_CAPTURE)) {
            $index = $matches[0][1];
            $output = mb_substr($fileName, 0, $index);
        } else {
            $output = pathinfo($fileName, PATHINFO_FILENAME);
        }

        return $output;
    }

    /**
     * @param string $mapName
     *
     * @return string
     */
    private function deriveNameForConstructionSiteImage(string $mapName)
    {
        $output = $this->deriveNameForFile($mapName);

        return $output;
    }

    /**
     * @param string $mapName
     *
     * @return string
     */
    private function deriveNameForMapFile(string $mapName)
    {
        $output = $this->deriveNameForFile($mapName);

        // replace _ with space
        $output = str_replace('_', ' ', $mapName);

        // add space before all capitals which are followed by at least 2 non-capital (ObergeschossHaus)
        $output = preg_replace('/(?<!^)([A-Z][a-z]{2,})/', ' $0', $output);

        // add space before all numbers (Haus2)
        $output = preg_replace('/(?<!^)([0-9]+)/', ' $0', $output);

        // add point after all numbers which are before any letters
        if (preg_match('/[a-zA-Z]/', $output, $matches, PREG_OFFSET_CAPTURE)) {
            $index = $matches[0][1];
            $before = mb_substr($output, 0, $index);
            $after = mb_substr($output, $index);

            // match only single numbers followed by a space (1 Obergeschoss)
            $output = preg_replace('/[0-9]{1}[ ]/', '$0.', $before) . $after;
        }

        // remove multiple whitespaces
        return preg_replace('/\s+/', ' ', $output);
    }

    /**
     * @param string[] $mapNames
     *
     * @return string[]
     */
    private function normalizeNames(array $mapNames)
    {
        //skip normalization if too few map names
        if (\count($mapNames) < 3) {
            return $mapNames;
        }

        // remove any entries occurring always
        /** @var string[][] $partsAnalytics */
        $partsAnalytics = [];
        $mapParts = [];

        // collect stats about file names
        foreach ($mapNames as $mapName) {
            $parts = explode(' ', $mapName);
            $mapParts[] = $parts;
            for ($i = 0; $i < \count($parts); ++$i) {
                if (!array_key_exists($i, $partsAnalytics)) {
                    $partsAnalytics[$i] = [];
                }

                $currentPart = $parts[$i];
                if (!array_key_exists($currentPart, $partsAnalytics[$i])) {
                    $partsAnalytics[$i][$currentPart] = 1;
                } else {
                    ++$partsAnalytics[$i][$currentPart];
                }
            }
        }

        // remove groups which are always the same
        for ($i = 0; $i < \count($partsAnalytics); ++$i) {
            // only one value; can safely remove because will not contain any useful information
            if (\count($partsAnalytics[$i]) === 1) {
                // remove from parts list
                foreach ($mapParts as &$mapPart) {
                    unset($mapPart[$i]);
                    $mapPart = array_values($mapPart);
                }

                //remove processed entry group
                unset($partsAnalytics[$i]);
                $partsAnalytics = array_values($partsAnalytics);
                --$i;
            }
        }

        // remove groups which are very likely date groups
        for ($i = 0; $i < \count($partsAnalytics); ++$i) {
            $probablyDateGroup = true;
            foreach ($partsAnalytics[$i] as $element => $counter) {
                if (!is_numeric($element)) {
                    $probablyDateGroup = false;
                    break;
                }

                $probableYear = mb_substr($element, 0, 2);
                $currentYear = mb_substr(date('Y'), 2, 2);
                if ($probableYear < 10 || $probableYear > $currentYear) {
                    $probablyDateGroup = false;
                    break;
                }
            }

            if ($probablyDateGroup) {
                // remove from parts list
                foreach ($mapParts as &$mapPart) {
                    unset($mapPart[$i]);
                    $mapPart = array_values($mapPart);
                }

                //remove processed entry group
                unset($partsAnalytics[$i]);
                $partsAnalytics = array_values($partsAnalytics);
                --$i;
            }
        }

        $counter = 0;
        $resultingNames = [];
        foreach ($mapNames as $key => $mapName) {
            // join parts back together
            $newName = implode(' ', $mapParts[$counter++]);

            // remove multiple whitespaces
            $newName = preg_replace('/\s+/', ' ', $newName);

            $resultingNames[$key] = $newName;
        }

        return $resultingNames;
    }
}
