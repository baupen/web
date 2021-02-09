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
     * constants for drawing the issues on the maps.
     */
    private const PADDING_PROPORTION = 0.375; // how much padding proportional to height is drawn around the issue number
    private const SINGLE_CHARACTER_SURFACE_PERCENTAGE = 0.005; // how much surface of the image a single character should take
    private const MAX_CHARACTERS_SURFACE_PERCENTAGE = 0.1; // how much surface of the image all characters should take
    private const MAXIMAL_CHARACTER_PROPORTION = 0.05; // how large a single character is allowed to be relative to the full image
    private const MINIMAL_CHARACTER_HEIGHT = 8.0; // minimal pixels a single character is allowed to be
    private const MAXIMAL_CHARACTER_HEIGHT = 30.0; // maximal pixels a single character is allowed to be

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
    public function renderMapFileWithIssuesToFile(MapFile $mapFile, array $issues, string $targetFilePath, $size = self::SIZE_THUMBNAIL): bool
    {
        $mapFileJpgPath = $this->renderMapFileToJpg($mapFile, $size);
        if (null === $mapFileJpgPath) {
            return false;
        }

        // render issues on image
        $image = imagecreatefromjpeg($mapFileJpgPath);
        $this->drawIssuesOnJpg($image, $issues);
        imagejpeg($image, $targetFilePath);

        return true;
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

    private function renderSizeFor(string $sourcePath, string $targetFolder, string $size): ?string
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
    private function drawIssuesOnJpg(&$image, array $issues): void
    {
        // estimate how much is drawn on the map
        $drawnIssues = [];
        $measurementFontSize = 30;

        $totalTextWidth = 0;
        $totalTextLength = 0;
        $totalTextHeight = 0;
        foreach ($issues as $issue) {
            if (null !== $issue->getPositionX()) {
                $circleColor = null !== $issue->getClosedAt() ? 'green' : 'orange';

                $issueText = (string) $issue->getNumber();
                list($textWidth, $textHeight) = $this->gdService->measureTextDimensions($measurementFontSize, $issueText);

                $drawnIssues[] = [
                    'text' => $issueText,
                    'x' => $issue->getPositionX(),
                    'y' => $issue->getPositionY(),
                    'color' => $circleColor,
                    'width' => $textWidth,
                    'height' => $textHeight,
                ];

                $totalTextWidth += $textWidth;
                $totalTextLength += mb_strlen($issueText);
                $totalTextHeight += $textHeight;
            }
        }

        if (0 === count($drawnIssues)) {
            return;
        }

        $averageTextHeight = $totalTextHeight / count($drawnIssues);
        $averageTextWidth = $totalTextWidth / $totalTextLength;
        $padding = $averageTextHeight * self::PADDING_PROPORTION;

        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $imageSurface = $xSize * $ySize;
        $textSurface = ($totalTextWidth + 2 * count($drawnIssues) * $padding) * ($averageTextHeight + 2 * $padding);

        $targetTextSurfaceShare = min(self::MAX_CHARACTERS_SURFACE_PERCENTAGE, self::SINGLE_CHARACTER_SURFACE_PERCENTAGE * $totalTextLength); // 0.5% for single issue; cap at 10%
        $actualTextSurfaceShare = $textSurface / $imageSurface;

        $optimalFontScale = sqrt($targetTextSurfaceShare / $actualTextSurfaceShare);
        // max: single character should not be larger than maxbounds of longer side
        if ($xSize < $ySize) {
            $fontScale = $this->ensureMaxBounds(self::MAXIMAL_CHARACTER_PROPORTION * $ySize, $averageTextHeight, $optimalFontScale);
        } else {
            $fontScale = $this->ensureMaxBounds(self::MAXIMAL_CHARACTER_PROPORTION * $xSize, $averageTextWidth, $optimalFontScale);
        }

        // min: single character should not be smaller
        $minFontScale = self::MINIMAL_CHARACTER_HEIGHT / $averageTextHeight;
        $fontScale = max($minFontScale, $fontScale);

        // max: single character should not be larger
        $maxFontScale = self::MAXIMAL_CHARACTER_HEIGHT / $averageTextHeight;
        $fontScale = min($maxFontScale, $fontScale);

        $actualPadding = $padding * $fontScale;
        $actualFontSize = $measurementFontSize * $fontScale;

        foreach ($drawnIssues as $issue) {
            $positionX = $issue['x'] * $xSize;
            $positionY = $issue['y'] * $ySize;
            $textWidth = $issue['width'] * $fontScale;
            $textHeight = $issue['height'] * $fontScale;
            $this->gdService->drawRectangleWithText($positionX, $positionY, $issue['color'], $actualPadding, $issue['text'], $actualFontSize, $textWidth, $textHeight, $image);
        }
    }

    private function ensureMaxBounds(float $maxSize, float $currentSize, float $currentMultiplier)
    {
        $resultSize = $currentSize * $currentMultiplier;

        return $currentMultiplier * min($maxSize / $resultSize, 1);
    }

    private function renderPdfToJpg(string $sourcePath, string $targetFolder): ?string
    {
        $pdfRenderPath = $targetFolder.DIRECTORY_SEPARATOR.self::PDF_RENDER_NAME;
        if (!file_exists($pdfRenderPath)) {
            FileHelper::ensureFolderExists($targetFolder);
            if (!$this->gsService->renderPdfToImage($sourcePath, $pdfRenderPath)) {
                return null;
            }

            //abort if creation failed
            if (!file_exists($pdfRenderPath)) {
                return $pdfRenderPath;
            }
        }

        return file_exists($pdfRenderPath) ? $pdfRenderPath : null;
    }
}
