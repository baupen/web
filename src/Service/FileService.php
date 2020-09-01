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

use App\Entity\Map;
use App\Entity\MapFile;
use App\Entity\Traits\FileTrait;
use App\Helper\FileHelper;
use App\Service\Interfaces\PathServiceInterface;

class FileService
{
    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * PdfOptimizationService constructor.
     */
    public function __construct(PathServiceInterface $pathService)
    {
        $this->pathService = $pathService;
    }

    /**
     * @return string|null
     */
    public function setMapFile(string $sourceFilePath, string $displayFileName, Map $map)
    {
        // copy file without collisions
        $fileName = basename($filePath);

        $mapFile = new MapFile();
        $mapFile->setFilename($fileName);
        $mapFile->setHash(hash_file('sha256', $filePath));

        $targetFolder = $this->pathService->getFolderForMapFile($map->getConstructionSite());
        FileHelper::copySingle($sourceFilePath, $targetFolder.DIRECTORY_SEPARATOR.$targetFileName);

        if (null === $entity->getFile()) {
            return null;
        }

        $originalFilePath = $this->pathService->getFolderForMapFile($entity->getConstructionSite()).\DIRECTORY_SEPARATOR.$entity->getFile()->getFilename();

        return $this->renderForMobileDevice($entity, $originalFilePath);
    }

    /**
     * @param FileTrait $fileTrait
     */
    private function writeFromFile(string $filePath, $fileTrait)
    {
    }

    /**
     * @return string|null
     */
    private function renderForMobileDevice(Map $map, string $sourceFilePath)
    {
        $targetFolder = $this->pathService->getTransientFolderForMapFile($map);

        if (!is_dir($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }

        $sourceFileName = pathinfo($sourceFilePath, PATHINFO_BASENAME);
        $extension = pathinfo($sourceFileName, PATHINFO_EXTENSION);
        $filenameWithoutEnding = mb_substr($sourceFileName, 0, -(mb_strlen($extension) + 1));

        $targetFilePath = $targetFolder.\DIRECTORY_SEPARATOR.$filenameWithoutEnding.'_outlines.'.$extension;

        if (!is_file($targetFilePath)) {
            // performance on iOS greatly improves with no output fonts
            $command = ' gs -dNoOutputFonts -sDEVICE=pdfwrite -o "'.$targetFilePath.'" "'.$sourceFilePath.'"';
            exec($command);
            if (!is_file($targetFilePath)) {
                return null;
            }
        }

        return $targetFilePath;
    }
}
