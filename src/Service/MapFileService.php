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

use App\Entity\MapFile;
use App\Helper\FileHelper;
use App\Service\Image\GsService;
use App\Service\Interfaces\MapFileServiceInterface;
use App\Service\Interfaces\PathServiceInterface;

class MapFileService implements MapFileServiceInterface
{
    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var GsService
     */
    private $gsService;

    /**
     * MapFileService constructor.
     */
    public function __construct(PathServiceInterface $pathService, GsService $gsService)
    {
        $this->pathService = $pathService;
        $this->gsService = $gsService;
    }

    /**
     * @return string|null
     */
    public function renderForMobileDevice(MapFile $mapFile)
    {
        $sourceFilePath = $this->pathService->getFolderForMapFiles($mapFile->getConstructionSite()).\DIRECTORY_SEPARATOR.$mapFile->getFilename();

        $targetFolder = $this->pathService->getTransientFolderForMapFile($mapFile);
        $fileName = pathinfo($sourceFilePath, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $targetFileName = $fileName.'_outlines_'.$extension;
        $targetFilePath = $targetFolder.DIRECTORY_SEPARATOR.$targetFileName;

        if (is_file($targetFilePath)) {
            return $targetFilePath;
        }

        FileHelper::ensureFolderExists($targetFolder);
        $this->gsService->renderPdfWithoutOutlineFonts($sourceFilePath, $targetFilePath);

        return is_file($targetFilePath) ? $targetFilePath : $sourceFilePath;
    }
}
