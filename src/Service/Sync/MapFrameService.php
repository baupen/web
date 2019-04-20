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
use App\Entity\MapFile;
use App\Model\Frame;
use App\Model\SyncTransaction;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Sync\Interfaces\DisplayNameServiceInterface;
use App\Service\Sync\Interfaces\FileServiceInterface;
use App\Service\Sync\Interfaces\MapFrameServiceInterface;
use const DIRECTORY_SEPARATOR;

class MapFrameService implements MapFrameServiceInterface
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
     * @var FileServiceInterface
     */
    private $fileService;

    public function __construct(PathServiceInterface $pathService, DisplayNameServiceInterface $displayNameService, FileServiceInterface $fileService)
    {
        $this->pathService = $pathService;
        $this->displayNameService = $displayNameService;
        $this->fileService = $fileService;
    }

    /**
     * @param SyncTransaction  $syncTransaction
     * @param ConstructionSite $constructionSite
     */
    public function syncMapFrames(SyncTransaction $syncTransaction, ConstructionSite $constructionSite)
    {
        /** @var MapFile[] $mapFiles */
        $mapFiles = $constructionSite->getMapFiles()->toArray();

        $directory = $this->pathService->getFolderForMapFile($constructionSite);
        foreach ($mapFiles as $mapFile) {
            $fileNameWithoutExtension = mb_substr($mapFile->getFilename(), 0, -3);

            $frameJsonPath = $directory . DIRECTORY_SEPARATOR . $fileNameWithoutExtension . 'sectors.frame.json';
            $frame = $this->parseFrame($frameJsonPath);
            $this->syncFrame($syncTransaction, $mapFile, $frame);
        }
    }

    /**
     * @param string $filePath
     *
     * @return Frame|null
     */
    private function parseFrame(string $filePath)
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $json = json_decode(file_get_contents($filePath));

        if (!property_exists($json, 'upper') ||
            !property_exists($json, 'lower') ||
            !property_exists($json, 'left') ||
            !property_exists($json, 'right')) {
            return null;
        }

        $frame = new Frame();
        $frame->startX = $json->left;
        $frame->startY = $json->right;
        $frame->width = $json->right - $json->left;
        $frame->height = $json->lower - $json->upper;

        return $frame;
    }

    /**
     * @param SyncTransaction $syncTransaction
     * @param MapFile         $mapFile
     * @param Frame|null      $frame
     */
    private function syncFrame(SyncTransaction $syncTransaction, MapFile $mapFile, ?Frame $frame)
    {
        if ($mapFile->getSectorFrame() === null && $frame === null) {
            return;
        }

        if ($mapFile->getSectorFrame() !== null && $frame !== null && !$frame->equals($mapFile->getSectorFrame())) {
            return;
        }

        $mapFile->setSectorFrame($frame);
        $syncTransaction->persist($mapFile);
    }
}
