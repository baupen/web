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
        $constructionSite->setName($this->displayNameService->forConstructionSite($folderName));
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
        $isCacheInvalidatedConstructionSite = false;
        $this->syncConstructionSite($directory, $constructionSite, $isCacheInvalidatedConstructionSite);

        $cacheInvalidatedMaps = [];
        $this->syncConstructionSiteMaps($directory, $constructionSite, $cacheInvalidatedMaps);

        $manager = $this->registry->getManager();
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
     * @param string $directory
     * @param ConstructionSite $constructionSite
     * @param bool $isCacheInvalidatedConstructionSite
     */
    private function syncConstructionSite(string $directory, ConstructionSite $constructionSite, bool &$isCacheInvalidatedConstructionSite)
    {
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
            $image->setDisplayFilename($this->displayNameService->forConstructionSiteImage($image->getFilename()));
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
    }

    /**
     * @param string $directory
     * @param ConstructionSite $constructionSite
     * @param array $cacheInvalidatedMaps
     */
    private function syncConstructionSiteMaps(string $directory, ConstructionSite $constructionSite, array $cacheInvalidatedMaps)
    {
        // get all files from the directory
        /** @var MapFile[] $existingMapFiles */
        $existingMapFiles = $this->registry->getRepository(MapFile::class)->findBy(['constructionSite' => $constructionSite->getId()]);
        $mapsDirectory = $directory . \DIRECTORY_SEPARATOR . 'maps';
        /** @var MapFile[] $newMapFiles */
        $newMapFiles = $this->getFiles($mapsDirectory, '.pdf', $existingMapFiles, function () {
            return new MapFile();
        });

        foreach ($newMapFiles as $newMapFile) {
            $newMapFile->setConstructionSite($constructionSite);
        }

        /** @var MapFile[] $allMapFiles */
        $allMapFiles = array_merge($newMapFiles, $existingMapFiles);

        // refresh recommended filenames
        $mapNames = [];
        foreach ($allMapFiles as $mapFile) {
            $mapFile->setDisplayFilename($this->displayNameService->forMapFile($mapFile->getFilename()));
            $mapNames[] = $mapFile->getDisplayFilename();
        }
        $mapNames = $this->displayNameService->normalizeMapNames($mapNames);
        $counter = 0;
        foreach ($allMapFiles as $mapFile) {
            $mapFile->setDisplayFilename($mapNames[$counter++]);
        }

        // sets map parent for all newly found map files
        $this->assignMapFilesToMaps($constructionSite, $allMapFiles);

        // put all in tree structure
        $this->createTreeStructure($constructionSite->getMaps()->toArray());
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param MapFile[] $mapFiles
     */
    private function assignMapFilesToMaps(ConstructionSite $constructionSite, $mapFiles)
    {
        /** @var Map[] $displayNameToMapLookup */
        $displayNameToMapLookup = [];
        foreach ($mapFiles as $mapFile) {
            if ($mapFile->getMap() === null) {
                continue;
            }

            $key = $mapFile->getDisplayFilename();
            if (!array_key_exists($key, $displayNameToMapLookup)) {
                $displayNameToMapLookup[$key] = $mapFile->getMap();
            } elseif ($displayNameToMapLookup[$key]->getPreventAutomaticEdit() && !$mapFile->getMap()->getPreventAutomaticEdit()) {
                $displayNameToMapLookup[$key] = $mapFile->getMap();
            }
        }

        /** @var Map[] $cacheInvalidatedMaps */
        $cacheInvalidatedMaps = [];
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
            }
        }
    }
}
