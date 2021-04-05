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
        $sourceFolder = $this->pathService->getFolderForIssueImages($issueImage->getCreatedFor()->getConstructionSite());
        $sourcePath = $sourceFolder.DIRECTORY_SEPARATOR.$issueImage->getFilename();
        $targetFolder = $this->pathService->getTransientFolderForIssueImage($issueImage);

        return $this->renderSizeFor($sourcePath, $targetFolder, $size);
    }

    public function resizeConstructionSiteImage(ConstructionSiteImage $constructionSiteImage, string $size = self::SIZE_THUMBNAIL): ?string
    {
        //setup paths
        $sourceFolder = $this->pathService->getFolderForConstructionSiteImages($constructionSiteImage->getCreatedFor());
        $sourcePath = $sourceFolder.DIRECTORY_SEPARATOR.$constructionSiteImage->getFilename();
        $targetFolder = $this->pathService->getTransientFolderForConstructionSiteImages($constructionSiteImage);

        return $this->renderSizeFor($sourcePath, $targetFolder, $size);
    }

    /**
     * @param Issue[] $issues
     */
    public function renderMapFileWithIssuesToJpg(MapFile $mapFile, array $issues, string $size = self::SIZE_THUMBNAIL): ?string
    {
        $mapFileJpgPath = $this->renderMapFileToJpg($mapFile, $size);
        if (null === $mapFileJpgPath) {
            return null;
        }

        $content = [];
        foreach ($issues as $issue) {
            if ($issue->hasPosition()) {
                $circleColor = null !== $issue->getClosedAt() ? 'green' : 'orange';
                $issueText = (string) $issue->getNumber();
                $content[] = [
                    'text' => $issueText,
                    'x' => $issue->getPositionX(),
                    'y' => $issue->getPositionY(),
                    'color' => $circleColor,
                ];
            }
        }

        $contentCount = count($content);
        if (0 === $contentCount) {
            return $mapFileJpgPath;
        }

        $contentHash = hash('sha256', serialize($content));
        $targetFolder = $this->pathService->getTransientFolderForMapFileRenders($mapFile);
        $targetFilePath = $this->getPathForSize($mapFileJpgPath, $targetFolder, $contentCount.'_'.$contentHash, $size);
        if (file_exists($targetFilePath)) {
            return $targetFilePath;
        }

        FileHelper::ensureFolderExists($targetFolder);
        $this->drawContentOnJpg($mapFileJpgPath, $targetFilePath, $content);

        //abort if generation failed
        if (!file_exists($targetFilePath)) {
            return null;
        }

        return $targetFilePath;
    }

    public function renderMapFileWithSingleIssueToJpg(MapFile $mapFile, Issue $issue, string $size = self::SIZE_THUMBNAIL): ?string
    {
        $mapFileJpgPath = $this->renderMapFileToJpg($mapFile, $size);
        if (null === $mapFileJpgPath) {
            return null;
        }

        if (!$issue->hasPosition()) {
            return $mapFileJpgPath;
        }

        $content = [
            'x' => $issue->getPositionX(),
            'y' => $issue->getPositionY(),
        ];

        $contentHash = hash('sha256', serialize($content));
        $targetFolder = $this->pathService->getTransientFolderForMapFileRenders($mapFile);
        $targetFilePath = $this->getPathForSize($mapFileJpgPath, $targetFolder, 'single_'.$issue->getNumber().'_'.$contentHash, $size);
        if (file_exists($targetFilePath)) {
            return $targetFilePath;
        }

        FileHelper::ensureFolderExists($targetFolder);
        $this->drawCrosshairOnJpg($mapFileJpgPath, $targetFilePath, $issue->getPositionX(), $issue->getPositionY());

        //abort if generation failed
        if (!file_exists($targetFilePath)) {
            return null;
        }

        return $targetFilePath;
    }

    public function renderMapFileToJpg(MapFile $mapFile, string $size = self::SIZE_THUMBNAIL): ?string
    {
        //setup paths
        $sourceFilePath = $this->pathService->getFolderForMapFiles($mapFile->getCreatedFor()->getConstructionSite()).DIRECTORY_SEPARATOR.$mapFile->getFilename();
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
        /** @var string $filename */
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
        $targetFilePath = $this->getPathForSize($sourcePath, $targetFolder, $filename, $size);
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

    private function getPathForSize(string $sourcePath, string $targetFolder, string $filename, string $size)
    {
        /** @var string $ending */
        $ending = pathinfo($sourcePath, PATHINFO_EXTENSION);

        $targetFileName = $filename.'_'.$size.'.'.$ending;

        return $targetFolder.DIRECTORY_SEPARATOR.$targetFileName;
    }

    private function drawCrosshairOnJpg(string $sourcePath, string $targetPath, float $positionX, float $positionY)
    {
        $image = imagecreatefromjpeg($sourcePath);
        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $maxSize = max($xSize, $ySize);

        $lineThickness = 5;
        $circleThickness = $lineThickness * 6;
        $radius = $circleThickness * 3;

        if ($maxSize > $radius * 2 * 6) {
            // more than 6 such crosshairs have space on the longer size
            $this->gdService->drawCrosshair($positionX * $xSize, $positionY * $ySize, 'blue', $radius, $circleThickness, $lineThickness, $image);
        } else {
            $lineThickness = 3;
            $circleThickness = $lineThickness * 3;
            $radius = $circleThickness * 5;

            if ($maxSize > $radius * 2 * 4) {
                // more than 4 such crosshairs have space on the longer size
                $this->gdService->drawCrosshair($positionX * $xSize, $positionY * $ySize, 'blue', $radius, $circleThickness, $lineThickness, $image);
            } else {
                // draw a dot only
                $dotSize = $maxSize / 6;
                $this->gdService->drawCrosshair($positionX * $xSize, $positionY * $ySize, 'blue', $dotSize / 2, $dotSize, 0, $image);
            }
        }

        imagejpeg($image, $targetPath);
    }

    /**
     * @param string[][] $content
     */
    private function drawContentOnJpg(string $sourcePath, string $targetPath, array $content): void
    {
        // estimate how much is drawn on the map
        $measurementFontSize = 30;
        $totalTextWidth = 0;
        $totalTextLength = 0;
        $totalTextHeight = 0;
        foreach ($content as &$entry) {
            list($textWidth, $textHeight) = $this->gdService->measureTextDimensions($measurementFontSize, $entry['text']);

            $entry['width'] = $textWidth;
            $entry['height'] = $textHeight;

            $totalTextWidth += $textWidth;
            $totalTextLength += mb_strlen($entry['text']);
            $totalTextHeight += $textHeight;
        }
        unset($entry);

        $averageTextHeight = $totalTextHeight / count($content);
        $averageTextWidth = $totalTextWidth / $totalTextLength;
        $padding = $averageTextHeight * self::PADDING_PROPORTION;

        $image = imagecreatefromjpeg($sourcePath);
        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $imageSurface = $xSize * $ySize;
        $textSurface = ($totalTextWidth + 2 * count($content) * $padding) * ($averageTextHeight + 2 * $padding);

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

        foreach ($content as $issue) {
            $positionX = $issue['x'] * $xSize;
            $positionY = $issue['y'] * $ySize;
            $textWidth = $issue['width'] * $fontScale;
            $textHeight = $issue['height'] * $fontScale;
            $this->gdService->drawRectangleWithText($positionX, $positionY, $issue['color'], $actualPadding, $issue['text'], $actualFontSize, $textWidth, $textHeight, $image);
        }

        imagejpeg($image, $targetPath);
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
