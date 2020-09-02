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

        $targetFolder = $this->pathService->getFolderForMapFiles($map->getConstructionSite());
        FileHelper::copySingle($sourceFilePath, $targetFolder.DIRECTORY_SEPARATOR.$targetFileName);

        if (null === $entity->getFile()) {
            return null;
        }

        $originalFilePath = $this->pathService->getFolderForMapFiles($entity->getConstructionSite()).\DIRECTORY_SEPARATOR.$entity->getFile()->getFilename();

        return $this->renderForMobileDevice($entity, $originalFilePath);
    }

    /**
     * @param FileTrait $fileTrait
     */
    private function writeFromFile(string $filePath, $fileTrait)
    {
    }
}
