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
use App\Service\Interfaces\MapFileServiceInterface;
use App\Service\Interfaces\PathServiceInterface;

class MapFileService implements MapFileServiceInterface
{
    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * PdfOptimizationService constructor.
     *
     * @param PathServiceInterface $pathService
     */
    public function __construct(PathServiceInterface $pathService)
    {
        $this->pathService = $pathService;
    }

    /**
     * @param Map $entity
     *
     * @return string|null
     */
    public function getForMobileDevice(Map $entity)
    {
        if ($entity->getFile() === null) {
            return null;
        }

        $originalFilePath = $this->pathService->getFolderForMapFile($entity->getConstructionSite()) . \DIRECTORY_SEPARATOR . $entity->getFile()->getFilename();

        return $this->renderForMobileDevice($entity, $originalFilePath);
    }

    /**
     * @param Map    $map
     * @param string $sourceFilePath
     *
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

        $targetFilePath = $targetFolder . \DIRECTORY_SEPARATOR . $filenameWithoutEnding . '_outlines.' . $extension;

        if (!is_file($targetFilePath)) {
            $command = ' gs -dNoOutputFonts -sDEVICE=pdfwrite -o "' . $targetFilePath . '" "' . $sourceFilePath . '"';
            exec($command);
            if (!is_file($targetFilePath)) {
                return null;
            }
        }

        return $targetFilePath;
    }
}
