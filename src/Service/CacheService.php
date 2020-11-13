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

use App\Entity\ConstructionSiteImage;
use App\Entity\IssueImage;
use App\Entity\MapFile;
use App\Service\Interfaces\CacheServiceInterface;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\MapFileServiceInterface;

class CacheService implements CacheServiceInterface
{
    /**
     * @var ImageServiceInterface
     */
    private $imageService;

    /**
     * @var MapFileServiceInterface
     */
    private $mapFileService;

    /**
     * CacheService constructor.
     */
    public function __construct(ImageServiceInterface $imageService, MapFileServiceInterface $mapFileService)
    {
        $this->imageService = $imageService;
        $this->mapFileService = $mapFileService;
    }

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     */
    public function warmUpCacheForIssueImage(IssueImage $issueImage)
    {
        foreach (ImageServiceInterface::VALID_SIZES as $validSize) {
            $this->imageService->resizeIssueImage($issueImage, $validSize);
        }
    }

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     */
    public function warmUpCacheForConstructionSiteImage(ConstructionSiteImage $constructionSiteImage)
    {
        foreach (ImageServiceInterface::VALID_SIZES as $validSize) {
            $this->imageService->resizeConstructionSiteImage($constructionSiteImage, $validSize);
        }
    }

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     */
    public function warmUpCacheForMapFile(MapFile $mapFile)
    {
        foreach (ImageServiceInterface::VALID_SIZES as $validSize) {
            $this->imageService->renderMapFileToJpg($mapFile, $validSize);
        }

        $this->mapFileService->renderForMobileDevice($mapFile);
    }
}
