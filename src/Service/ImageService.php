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
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Helper\FileHelper;
use App\Service\Image\GdService;
use App\Service\Image\GsService;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use const DIRECTORY_SEPARATOR;

class ImageService implements ImageServiceInterface
{
    /**
     * the name of the image rendered from the map pdf.
     */
    private const PDF_RENDER_NAME = 'render.jpg';

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var GdService
     */
    private $gdService;

    /**
     * @var GsService
     */
    private $gsService;

    /**
     * ImageService constructor.
     */
    public function __construct(PathServiceInterface $pathService, GdService $gdService, GsService $gsService)
    {
        $this->pathService = $pathService;
        $this->gdService = $gdService;
        $this->gsService = $gsService;
    }

    public function resizeIssueImage(IssueImage $issueImage, string $size = self::SIZE_THUMBNAIL): ?string
    {
        //setup paths
        $sourceFolder = $this->pathService->getFolderForIssueImages($issueImage->getIssue()->getMap()->getConstructionSite());
        $sourcePath = $sourceFolder.DIRECTORY_SEPARATOR.$issueImage->getFilename();
        $targetFolder = $this->pathService->getTransientFolderForIssueImage($issueImage);

        return $this->renderSizeFor($sourcePath, $targetFolder, $size);
    }

    public function resizeConstructionSiteImage(ConstructionSiteImage $constructionSiteImage, string $size = self::SIZE_THUMBNAIL): ?string
    {
        //setup paths
        $sourceFolder = $this->pathService->getFolderForConstructionSiteImages($constructionSiteImage->getConstructionSite());
        $sourcePath = $sourceFolder.DIRECTORY_SEPARATOR.$constructionSiteImage->getFilename();
        $targetFolder = $this->pathService->getTransientFolderForConstructionSiteImages($constructionSiteImage);

        return $this->renderSizeFor($sourcePath, $targetFolder, $size);
    }

    /**
     * @param Issue[] $issues
     * @param string  $size
     */
    public function renderMapFileWithIssues(MapFile $mapFile, array $issues, $size = self::SIZE_THUMBNAIL)
    {
        $mapFileJpgPath = $this->renderMapFileToJpg($mapFile, $size);
        if (null === $mapFileJpgPath) {
            return null;
        }

        // render issues on image
        $image = imagecreatefromjpeg($mapFileJpgPath);
        $this->drawIssuesOnJpg($image, $issues);

        return $image;
    }

    public function renderMapFileToJpg(MapFile $mapFile, string $size = self::SIZE_THUMBNAIL): ?string
    {
        //setup paths
        $sourceFilePath = $this->pathService->getFolderForMapFiles($mapFile->getConstructionSite()).DIRECTORY_SEPARATOR.$mapFile->getFilename();
        $targetFolder = $this->pathService->getTransientFolderForMapFile($mapFile);

        // render pdf
        $renderedPdf = $this->renderPdfToJpg($sourceFilePath, $targetFolder);
        if (null === $renderedPdf) {
            return null;
        }

        // render size
        return $this->renderSizeFor($renderedPdf, $targetFolder, $size);
    }

    /**
     * @param $size
     *
     * @return string
     */
    private function renderSizeFor(string $sourcePath, string $targetFolder, $size)
    {
        //setup paths
        $ending = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $fileName = pathinfo($sourcePath, PATHINFO_FILENAME);
        $targetFileName = $fileName.'_'.$size.'.'.$ending;
        $targetFilePath = $targetFolder.DIRECTORY_SEPARATOR.$targetFileName;

        //return if already created
        if (file_exists($targetFilePath)) {
            return $targetFilePath;
        }

        //generate variant
        FileHelper::ensureFolderExists($targetFolder);
        switch ($size) {
            case ImageServiceInterface::SIZE_THUMBNAIL:
                $this->gdService->resizeImage($sourcePath, $targetFilePath, 100, 80);
                break;
            case ImageServiceInterface::SIZE_PREVIEW:
                $this->gdService->resizeImage($sourcePath, $targetFilePath, 600, 877);
                break;
            case ImageServiceInterface::SIZE_FULL:
                $this->gdService->resizeImage($sourcePath, $targetFilePath, 2480, 3508);
                break;
        }

        //abort if generation failed
        if (!file_exists($targetFilePath)) {
            return null;
        }

        return $targetFilePath;
    }

    /**
     * @param Issue[] $issues
     */
    private function drawIssuesOnJpg(&$image, array $issues)
    {
        $xSize = imagesx($image);
        $ySize = imagesy($image);

        //draw the issues on the map
        foreach ($issues as $issue) {
            if (null !== $issue->getPositionX()) {
                $yCoordinate = $issue->getPositionX() * $ySize;
                $xCoordinate = $issue->getPositionY() * $xSize;
                $circleColor = null !== $issue->getReviewedAt() ? 'green' : 'orange';
                $this->gdService->drawRectangleWithText($yCoordinate, $xCoordinate, $circleColor, (string) $issue->getNumber(), $image);
            }
        }

        return $image;
    }

    private function renderPdfToJpg(string $sourcePath, string $targetFolder)
    {
        $pdfRenderPath = $targetFolder.DIRECTORY_SEPARATOR.self::PDF_RENDER_NAME;
        if (!file_exists($pdfRenderPath)) {
            FileHelper::ensureFolderExists($targetFolder);
            $this->gsService->renderPdfToImage($sourcePath, $pdfRenderPath);

            //abort if creation failed
            if (!file_exists($pdfRenderPath)) {
                return $pdfRenderPath;
            }
        }

        return $pdfRenderPath;
    }
}
