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
use App\Entity\ConstructionSiteImage;
use App\Model\SyncTransaction;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Sync\Interfaces\ConstructionSiteServiceInterface;
use App\Service\Sync\Interfaces\DisplayNameServiceInterface;
use App\Service\Sync\Interfaces\FileServiceInterface;
use App\Service\Sync\Interfaces\MapServiceInterface;
use function count;
use const DIRECTORY_SEPARATOR;

class ConstructionSiteService implements ConstructionSiteServiceInterface
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
     * @var MapServiceInterface
     */
    private $mapService;

    /**
     * @var FileServiceInterface
     */
    private $fileService;

    /**
     * ConstructionSiteService constructor.
     *
     * @param PathServiceInterface $pathService
     * @param DisplayNameServiceInterface $displayNameService
     * @param MapServiceInterface $mapSyncService
     * @param FileServiceInterface $fileService
     */
    public function __construct(PathServiceInterface $pathService, DisplayNameServiceInterface $displayNameService, MapServiceInterface $mapSyncService, FileServiceInterface $fileService)
    {
        $this->pathService = $pathService;
        $this->displayNameService = $displayNameService;
        $this->mapService = $mapSyncService;
        $this->fileService = $fileService;
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param string $directory
     */
    public function addConstructionSite(SyncTransaction $syncTransaction, string $directory)
    {
        $folderName = mb_substr($directory, mb_strrpos($directory, DIRECTORY_SEPARATOR) + 1);
        $constructionSite = new ConstructionSite();
        $constructionSite->setFolderName($folderName);
        $constructionSite->setName($this->displayNameService->forConstructionSite($folderName));

        $syncTransaction->persist($constructionSite);
        $this->syncConstructionSite($syncTransaction, $constructionSite);
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param ConstructionSite $constructionSite
     */
    public function syncConstructionSite(SyncTransaction $syncTransaction, ConstructionSite $constructionSite)
    {
        /**
         * conventions:
         * *.jpgs containing visualizations of the construction site are inside the /images folder
         * pdfs/dwgs containing maps are inside the /maps folder
         * if a pdf/dwg/jpg file should be added, but one already exists with a different hash, the new file is created as "<original_filename>_duplicate_<datetime>.<original_extension>
         * for example, if the file "preview.jpg" already exists, a file "preview__duplicate_2018-01-01T13_55.jpg" is added if it does not exist already.
         * no file other than of type json is ever replaced/removed; only add is allowed.
         */
        $constructionSiteImages = $constructionSite->getImages()->toArray();

        $this->findNewConstructionSiteImages($syncTransaction, $constructionSite, $constructionSiteImages);

        $this->refreshConstructionSiteImageFileNames($syncTransaction, $constructionSiteImages);

        $this->chooseMostAppropriateImageForConstructionSite($syncTransaction, $constructionSite, $constructionSiteImages);

        $this->mapService->syncConstructionSiteMaps($syncTransaction, $constructionSite);
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
        if ($constructionSite->getIsAutomaticEditEnabled()) {
            if ($constructionSite->getImage() !== null) {
                foreach ($constructionSiteImages as $possibleMatch) {
                    if ($constructionSite->getImage()->getDisplayFilename() === $possibleMatch->getDisplayFilename() &&
                        ($possibleMatch->getCreatedAt() === null || $possibleMatch->getCreatedAt() > $constructionSite->getImage()->getCreatedAt())) {
                        //replace match & stop
                        $constructionSite->setImage($possibleMatch);
                        $syncTransaction->persist($constructionSite);
                    }
                }
            } elseif (count($constructionSiteImages) > 0) {
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
        $newConstructionSiteImages = $this->fileService->getNewFiles($constructionSiteImagesDirectory, '.jpg', $constructionSiteImages, function () {
            return new ConstructionSiteImage();
        });

        foreach ($newConstructionSiteImages as $newConstructionSiteImage) {
            $newConstructionSiteImage->setConstructionSite($constructionSite);
            $constructionSite->getImages()->add($newConstructionSiteImage);
            $syncTransaction->persist($newConstructionSiteImage);
            $constructionSiteImages[] = $newConstructionSiteImage;
        }
    }
}
