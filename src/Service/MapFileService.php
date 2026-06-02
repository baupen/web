<?php

namespace App\Service;

use App\Entity\MapFile;
use App\Helper\FileHelper;
use App\Service\Image\GsService;
use App\Service\Interfaces\MapFileServiceInterface;
use App\Service\Interfaces\PathServiceInterface;

readonly class MapFileService implements MapFileServiceInterface
{
    public function __construct(private PathServiceInterface $pathService, private GsService $gsService)
    {
    }

    public function renderForMobileDevice(MapFile $mapFile): ?string
    {
        $sourceFilePath = $this->pathService->getFolderForMapFiles($mapFile->getCreatedFor()->getConstructionSite()) . \DIRECTORY_SEPARATOR . $mapFile->getFilename();

        $targetFolder = $this->pathService->getTransientFolderForMapFile($mapFile);
        /** @var string $fileName */
        $fileName = pathinfo($sourceFilePath, PATHINFO_FILENAME);
        /** @var string $extension */
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $targetFileName = $fileName . '_outlines_' . $extension;
        $targetFilePath = $targetFolder . DIRECTORY_SEPARATOR . $targetFileName;

        if (is_file($targetFilePath)) {
            return $targetFilePath;
        }

        FileHelper::ensureFolderExists($targetFolder);
        if (!$this->gsService->renderPdfWithoutOutlineFonts($sourceFilePath, $targetFilePath)) {
            return null;
        }

        return is_file($targetFilePath) ? $targetFilePath : $sourceFilePath;
    }
}
