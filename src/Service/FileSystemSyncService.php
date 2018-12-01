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
use App\Entity\Map;
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
     * @param string $directory
     * @param ConstructionSite $constructionSite
     */
    private function syncDirectory(string $directory, ConstructionSite $constructionSite)
    {
        // add file if it does not exist already
        $previewImageFilename = 'preview.jpg';
        $previewImagePath = $directory . \DIRECTORY_SEPARATOR . $previewImageFilename;
        if ($constructionSite->getImageFilename() === null && file_exists($previewImagePath)) {
            $constructionSite->setImageFilename($previewImageFilename);
            $this->imageService->warmupCacheForConstructionSite($constructionSite);
            //TODO: update hash of construction site preview file
        }

        // add maps which do not exist already
        $mapFolder = $this->pathService->getFolderForMap($constructionSite);
        $mapFolderLength = \mb_strlen($mapFolder);
        $existingMaps = glob($mapFolder . \DIRECTORY_SEPARATOR . '*.pdf');

        /** @var Map[] $mapLookup */
        $mapLookup = [];
        foreach ($constructionSite->getMaps() as $map) {
            $mapLookup[$map->getFilename()] = $map;
        }

        // create map object for each file not found
        $newMaps = [];
        foreach ($existingMaps as $existingMap) {
            $fileName = mb_substr($existingMap, $mapFolderLength + 1);
            if (!array_key_exists($fileName, $mapLookup)) {
                $map = new Map();
                $map->setConstructionSite($constructionSite);
                $map->setFilename($fileName);
                $newMaps[] = $map;
            }

            //TODO: update hash of map file
        }
        /** @var Map[] $allMaps */
        $allMaps = array_merge($newMaps, array_values($mapLookup));

        // derive a fitting name for the map depending on file name
        $mapNames = [];
        foreach ($allMaps as $item) {
            $mapNames[] = $this->deriveNameForMap($item->getFilename());
        }

        // normalize map names looking at all names given & write back
        $newNames = $this->normalizeNames($mapNames);
        $counter = 0;
        foreach ($allMaps as $map) {
            $map->setName($newNames[$counter++]);
        }

        // remove duplicates based on map name
        $this->removeDuplicates($constructionSite->getMaps(), $newMaps);

        // still remaining maps are added
        foreach ($newMaps as $newMap) {
            $constructionSite->getMaps()->add($newMap);
        }

        // put all in tree structure
        $this->createTreeStructure($constructionSite->getMaps()->toArray());

        // warmup cache for new maps
        foreach ($newMaps as $map) {
            $this->imageService->warmupCacheForMap($map);
        }

        // persist all changes
        $manager = $this->registry->getManager();
        foreach ($constructionSite->getMaps() as $map) {
            if (!$map->getPreventAutomaticEdit()) {
                $manager->persist($map);
            }
        }
        $manager->persist($constructionSite);
        $manager->flush();
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
            $parts = explode(' ', $map);
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
                    $map->setParent($parent);
                    break;
                }
            }
        }
    }

    /**
     * finds already existing maps and replaces values from the newly created duplicate.
     *
     * @param Map[] $existingMaps
     * @param Map[] $newMaps
     */
    private function removeDuplicates(array $existingMaps, array &$newMaps)
    {
        /** @var Map[] $existingMapLookup */
        $existingMapLookup = [];
        foreach ($existingMaps as $item) {
            $existingMapLookup[$item->getName()] = $item;
        }

        for ($i = 0; $i < \count($newMaps); ++$i) {
            // TODO: if more than one refreshed files exist, this fails
            if (array_key_exists($newMaps[$i]->getName(), $existingMapLookup)) {
                $newMap = $newMaps[$i];
                $existingMap = $existingMapLookup[$newMap->getName()];
                $existingMap->setFilename($newMap->getFilename());

                // remove entry from array & reset
                unset($newMaps[$i]);
                $newMaps = array_values($newMaps);
                --$i;
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
     * @param string $mapName
     *
     * @return string
     */
    private function deriveNameForMap(string $mapName)
    {
        $output = str_replace('_', ' ', $mapName);

        // add space before all capitals which are followed by at least 2 non-capital (ObergeschossHaus)
        $output = preg_replace('/(?<!^)([A-Z][a-z]{2,})/', ' $0', $output);

        // add space before all numbers (Haus2)
        $output = preg_replace('/(?<!^)([0-9]+)/', ' $0', $output);

        // add point after all numbers which are before any letters
        if (preg_match('[a-zA-Z]', $output, $matches, PREG_OFFSET_CAPTURE)) {
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
