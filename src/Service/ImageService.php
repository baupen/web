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

use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Helper\ImageHelper;
use App\Model\Frame;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use const DIRECTORY_SEPARATOR;
use Symfony\Component\HttpKernel\KernelInterface;

class ImageService implements ImageServiceInterface
{
    /**
     * the name of the image rendered from the map pdf.
     */
    private const MAP_RENDER_NAME = 'render.jpg';

    /**
     * @var array
     */
    private $validSizes = [ImageServiceInterface::SIZE_FULL, ImageServiceInterface::SIZE_REPORT_ISSUE, ImageServiceInterface::SIZE_REPORT_MAP, ImageServiceInterface::SIZE_SHARE_VIEW, ImageServiceInterface::SIZE_THUMBNAIL, ImageServiceInterface::SIZE_MEDIUM];

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var int the bubble size as an abstract unit
     *          the higher the number the smaller the resulting bubble
     */
    private $bubbleScale = 500;

    /**
     * @var bool if the cache should be disabled
     */
    private $disableCache = false;

    /**
     * @var bool prevents calls to warmup cache from archiving something
     */
    private $preventCacheWarmUp;

    /**
     * ImageService constructor.
     *
     * @param PathServiceInterface $pathService
     * @param KernelInterface      $kernel
     */
    public function __construct(PathServiceInterface $pathService, KernelInterface $kernel)
    {
        $this->pathService = $pathService;

        // improves performance when generating fixtures (done extensively in dev / test environment)
        $this->preventCacheWarmUp = $kernel->getEnvironment() !== 'prod';
    }

    /**
     * @param Map     $map
     * @param Issue[] $issues
     * @param string  $size
     *
     * @return string|null
     */
    public function generateMapImage(Map $map, array $issues, $size = self::SIZE_THUMBNAIL)
    {
        if ($map->getFile() === null) {
            return null;
        }

        //setup paths
        $sourceFilePath = $this->pathService->getFolderForMapFile($map->getConstructionSite()) . DIRECTORY_SEPARATOR . $map->getFile()->getFilename();
        $generationTargetFolder = $this->pathService->getTransientFolderForMapFile($map);
        $this->ensureFolderExists($generationTargetFolder);

        return $this->generateMapImageInternal($issues, $sourceFilePath, $generationTargetFolder, false, $size);
    }

    /**
     * @param Map    $map
     * @param array  $issues
     * @param string $size
     *
     * @return string|null
     */
    public function generateMapImageForReport(Map $map, array $issues, $size = self::SIZE_THUMBNAIL)
    {
        if ($map->getFile() === null) {
            return null;
        }

        //setup paths
        $sourceFilePath = $this->pathService->getFolderForMapFile($map->getConstructionSite()) . DIRECTORY_SEPARATOR . $map->getFile()->getFilename();
        $generationTargetFolder = $this->pathService->getTransientFolderForMapFile($map);
        $this->ensureFolderExists($generationTargetFolder);

        return $this->generateMapImageInternal($issues, $sourceFilePath, $generationTargetFolder, true, $size);
    }

    /**
     * @param MapFile $mapFile
     * @param string  $size
     *
     * @return string|null
     */
    public function getMapFileImage(MapFile $mapFile, $size = self::SIZE_FULL)
    {
        //setup paths
        $sourceFilePath = $this->pathService->getFolderForMapFile($mapFile->getConstructionSite()) . DIRECTORY_SEPARATOR . $mapFile->getFilename();
        $generationTargetFolder = $this->pathService->getTransientFolderForMapFile($mapFile->getMap());
        $this->ensureFolderExists($generationTargetFolder);

        $pdfRenderPath = $this->getRenderedPdfPath($sourceFilePath, $generationTargetFolder);
        if (!$pdfRenderPath) {
            return null;
        }

        return $this->renderSizeVariant($pdfRenderPath, $size);
    }

    /**
     * @param MapFile $mapFile
     * @param string  $size
     *
     * @return string|null
     */
    public function getMapFileSectorFrameImage(MapFile $mapFile, $size = self::SIZE_FULL)
    {
        //setup paths
        $sourceFilePath = $this->pathService->getFolderForMapFile($mapFile->getConstructionSite()) . DIRECTORY_SEPARATOR . $mapFile->getFilename();
        $generationTargetFolder = $this->pathService->getTransientFolderForMapFile($mapFile->getMap());
        $this->ensureFolderExists($generationTargetFolder);

        $pdfRenderPath = $this->getRenderedPdfPath($sourceFilePath, $generationTargetFolder);
        if (!$pdfRenderPath) {
            return null;
        }

        $targetPath = $generationTargetFolder . DIRECTORY_SEPARATOR . 'sector_frame.jpg';
        $croppedImagePath = $this->cropImage($pdfRenderPath, $targetPath, $mapFile->getSectorFrame());
        if (!$croppedImagePath) {
            return null;
        }

        return $this->renderSizeVariant($croppedImagePath, $size);
    }

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     *
     * @param Issue $issue
     */
    public function warmUpCacheForIssue(Issue $issue)
    {
        if ($issue->getImage() === null || $this->preventCacheWarmUp) {
            return;
        }

        //setup paths
        $sourceFolder = $this->pathService->getFolderForIssueImage($issue->getMap()->getConstructionSite());
        $targetFolder = $this->pathService->getTransientFolderForIssueImage($issue);
        $this->ensureFolderExists($targetFolder);

        foreach ($this->validSizes as $validSize) {
            $this->renderSizeFor($issue->getImage()->getFilename(), $sourceFolder, $targetFolder, $validSize);
        }
    }

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     *
     * @param ConstructionSite $constructionSite
     */
    public function warmUpCacheForConstructionSite(ConstructionSite $constructionSite)
    {
        if ($constructionSite->getImage() === null || $this->preventCacheWarmUp) {
            return;
        }

        //setup paths
        $sourceFolder = $this->pathService->getFolderForConstructionSiteImage($constructionSite);
        $targetFolder = $this->pathService->getTransientFolderForConstructionSiteImage($constructionSite);
        $this->ensureFolderExists($targetFolder);

        foreach ($this->validSizes as $validSize) {
            $this->renderSizeFor($constructionSite->getImage()->getFilename(), $sourceFolder, $targetFolder, $validSize);
        }
    }

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     *
     * @param Map $map
     */
    public function warmUpCacheForMap(Map $map)
    {
        if ($map->getFile() === null || $this->preventCacheWarmUp) {
            return;
        }

        //setup paths
        $sourceFilePath = $this->pathService->getFolderForMapFile($map->getConstructionSite()) . DIRECTORY_SEPARATOR . $map->getFile()->getFilename();
        $generationTargetFolder = $this->pathService->getTransientFolderForMapFile($map);
        $this->ensureFolderExists($generationTargetFolder);

        //pre-render all sizes
        foreach ($this->validSizes as $validSize) {
            $this->generateMapImageInternal([], $sourceFilePath, $generationTargetFolder, false, $validSize);
        }
    }

    /**
     * @param string $uncheckedSize
     *
     * @return string
     */
    public function ensureValidSize($uncheckedSize)
    {
        return \in_array($uncheckedSize, $this->validSizes, true) ? $uncheckedSize : ImageServiceInterface::SIZE_THUMBNAIL;
    }

    /**
     * @param Issue  $issue
     * @param string $size
     *
     * @return string|null
     */
    public function getSizeForIssue(Issue $issue, $size = self::SIZE_THUMBNAIL)
    {
        if ($issue->getImage() === null) {
            return null;
        }

        //setup paths
        $sourceFolder = $this->pathService->getFolderForIssueImage($issue->getMap()->getConstructionSite());
        $targetFolder = $this->pathService->getTransientFolderForIssueImage($issue);
        $this->ensureFolderExists($targetFolder);

        return $this->renderSizeFor($issue->getImage()->getFilename(), $sourceFolder, $targetFolder, $size);
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param string           $size
     *
     * @return string|null
     */
    public function getSizeForConstructionSite(ConstructionSite $constructionSite, $size = self::SIZE_THUMBNAIL)
    {
        if ($constructionSite->getImage() === null) {
            return null;
        }

        //setup paths
        $sourceFolder = $this->pathService->getFolderForConstructionSiteImage($constructionSite);
        $targetFolder = $this->pathService->getTransientFolderForConstructionSiteImage($constructionSite);
        $this->ensureFolderExists($targetFolder);

        return $this->renderSizeFor($constructionSite->getImage()->getFilename(), $sourceFolder, $targetFolder, $size);
    }

    /**
     * @param string|null $sourceFileName
     * @param string      $sourceFolder
     * @param string      $targetFolder
     * @param $size
     *
     * @return string
     */
    private function renderSizeFor(?string $sourceFileName, string $sourceFolder, string $targetFolder, $size)
    {
        if ($sourceFileName === null) {
            return null;
        }

        //setup paths
        $sourceFilePath = $sourceFolder . DIRECTORY_SEPARATOR . $sourceFileName;
        $targetFileName = $this->getSizeFilename($sourceFileName, $size);
        $targetFilePath = $targetFolder . DIRECTORY_SEPARATOR . $targetFileName;

        if (!file_exists($targetFilePath) || $this->disableCache) {
            $this->renderSizeOfImage($sourceFilePath, $targetFilePath, $size);

            //abort if generation failed
            if (!file_exists($targetFilePath)) {
                return null;
            }
        }

        return $targetFilePath;
    }

    /**
     * @param string $sourceFilePath
     * @param string $targetFilePath
     * @param string $size
     */
    private function renderSizeOfImage(string $sourceFilePath, string $targetFilePath, $size = ImageServiceInterface::SIZE_THUMBNAIL)
    {
        //generate variant if possible
        switch ($size) {
            case ImageServiceInterface::SIZE_THUMBNAIL:
                $this->resizeImage($sourceFilePath, $targetFilePath, 100, 50);
                break;
            case ImageServiceInterface::SIZE_SHARE_VIEW:
                $this->resizeImage($sourceFilePath, $targetFilePath, 450, 600);
                break;
            case ImageServiceInterface::SIZE_REPORT_ISSUE:
                $this->resizeImage($sourceFilePath, $targetFilePath, 600, 600);
                break;
            case ImageServiceInterface::SIZE_MEDIUM:
                $this->resizeImage($sourceFilePath, $targetFilePath, 600, 600);
                break;
            case ImageServiceInterface::SIZE_FULL:
                $this->resizeImage($sourceFilePath, $targetFilePath, 1920, 1080);
                break;
            case ImageServiceInterface::SIZE_REPORT_MAP:
                $this->resizeImage($sourceFilePath, $targetFilePath, 2480, 2480);
                break;
        }
    }

    /**
     * @param string $sourcePdfPath
     * @param string $targetFilepath
     */
    private function renderPdfToImage(string $sourcePdfPath, string $targetFilepath)
    {
        //do first low quality render to get artboxsize
        $command = 'gs -sDEVICE=jpeg -dDEVICEWIDTHPOINTS=1920 -dDEVICEHEIGHTPOINTS=1080 -dJPEGQ=10 -dUseCropBox -sPageList=1 -o "' . $targetFilepath . '" "' . $sourcePdfPath . '"';
        exec($command);
        if (!is_file($targetFilepath)) {
            return;
        }

        //second render with correct image dimensions
        list($width, $height) = ImageHelper::getWidthHeightArguments($targetFilepath, 3840, 2160);
        $command = 'gs -sDEVICE=jpeg -dDEVICEWIDTHPOINTS=' . $width . ' -dDEVICEHEIGHTPOINTS=' . $height . ' -dJPEGQ=80 -dUseCropBox -dFitPage -sPageList=1 -o "' . $targetFilepath . '" "' . $sourcePdfPath . '"';
        exec($command);
    }

    /**
     * @param Issue $issue
     * @param bool  $rotated
     * @param $image
     */
    private function drawIssue(Issue $issue, bool $rotated, &$image)
    {
        //get sizes
        $xSize = imagesx($image);
        $ySize = imagesy($image);

        //target location
        $position = $issue->getPosition();
        if ($rotated) {
            $yCoordinate = $position->getPositionX();
            $xCoordinate = $position->getPositionY();
        } else {
            $yCoordinate = $position->getPositionY();
            $xCoordinate = $position->getPositionX();
        }
        $yCoordinate *= $ySize;
        $xCoordinate *= $xSize;

        //colors sometime do not work and show up as black. just choose another color as close as possible to workaround
        if ($issue->getReviewedAt() !== null) {
            //green
            $circleColor = $this->createColor($image, 18, 140, 45);
        } else {
            //orange
            $circleColor = $this->createColor($image, 201, 151, 0);
        }

        $this->drawCircleWithText($yCoordinate, $xCoordinate, $circleColor, (string) $issue->getNumber(), $image);
    }

    /**
     * @param float $yPosition
     * @param float $xPosition
     * @param $circleColor
     * @param $text
     * @param $image
     */
    private function drawCircleWithText($yPosition, $xPosition, $circleColor, $text, &$image)
    {
        //get sizes
        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $imageSize = $xSize * $ySize;
        $targetTextDimension = sqrt($imageSize / ($this->bubbleScale * M_PI));

        //get text dimensions
        $font = __DIR__ . '/../../assets/fonts/OpenSans-Bold.ttf';
        $testFontSize = 30;
        $txtSize = imagettfbbox($testFontSize, 0, $font, $text);
        $testTextWidth = abs($txtSize[4] - $txtSize[0]);
        $testTextHeight = abs($txtSize[5] - $txtSize[1]);

        //calculate appropriate font size
        $maxTextDimension = max($testTextWidth, $testTextHeight * 1.4); //*1.4 to counter single number being too big
        $scalingFactor = $targetTextDimension / $maxTextDimension;
        $fontSize = $scalingFactor * $testFontSize;
        $textWidth = $testTextWidth * $scalingFactor;
        $textHeight = $testTextHeight * $scalingFactor;

        //calculate diameter around text
        $diameter = $targetTextDimension * 1.6; //*1.6 to have 0.3 at each circle end

        //draw white base ellipse before the colored one
        $white = $this->createColor($image, 255, 255, 255);
        imagefilledellipse($image, (int) $xPosition, (int) $yPosition, (int) ($diameter + 2), (int) ($diameter + 2), $white);
        imagefilledellipse($image, (int) $xPosition, (int) $yPosition, (int) $diameter, (int) $diameter, $circleColor);

        //draw text
        imagettftext($image, $fontSize, 0, (int) ($xPosition - ($textWidth / 2)), (int) ($yPosition + ($textHeight / 2)), $white, $font, $text);
    }

    /**
     * @param resource $image
     * @param int      $red
     * @param int      $green
     * @param int      $blue
     *
     * @return int
     */
    private function createColor($image, $red, $green, $blue)
    {
        //get color from palette
        $color = imagecolorexact($image, $red, $green, $blue);
        if ($color === -1) {
            //color does not exist...
            //test if we have used up palette
            if (imagecolorstotal($image) >= 255) {
                //palette used up; pick closest assigned color
                $color = imagecolorclosest($image, $red, $green, $blue);
            } else {
                //palette NOT used up; assign new color
                $color = imagecolorallocate($image, $red, $green, $blue);
            }
        }

        return $color;
    }

    /**
     * render issues on image if it does not already exist.
     *
     * @param Issue[] $issues
     * @param string  $pdfRenderPath
     * @param string  $issueImagePath
     * @param string  $landscapeIssueImagePath
     * @param bool    $forceLandscape
     *
     * @return string
     */
    private function renderIssues(array $issues, $pdfRenderPath, $issueImagePath, $landscapeIssueImagePath, $forceLandscape)
    {
        $targetImagePath = null;
        $sourceImageStream = null;
        $rotated = false;
        if ($forceLandscape) {
            if (!is_file($landscapeIssueImagePath) || $this->disableCache) {
                $targetImagePath = $landscapeIssueImagePath;
                $sourceImageStream = imagecreatefromjpeg($pdfRenderPath);
                $width = imagesx($sourceImageStream);
                $height = imagesy($sourceImageStream);

                if ($height > $width) {
                    $sourceImageStream = imagerotate($sourceImageStream, 90, 0);
                    $rotated = true;
                } elseif (file_exists($issueImagePath)) {
                    //simply copy already rendered file
                    copy($issueImagePath, $landscapeIssueImagePath);
                    imagedestroy($sourceImageStream);
                    $sourceImageStream = null;
                }
            }
        } elseif (!is_file($issueImagePath) || $this->disableCache) {
            $targetImagePath = $issueImagePath;
            $sourceImageStream = imagecreatefromjpeg($pdfRenderPath);
        }

        //render if needed
        if ($sourceImageStream !== null) {
            //draw the issues on the map
            foreach ($issues as $issue) {
                if ($issue->getPosition() !== null) {
                    $this->drawIssue($issue, $rotated, $sourceImageStream);
                }
            }

            //write to disk & destroy
            imagejpeg($sourceImageStream, $targetImagePath, 90);
            imagedestroy($sourceImageStream);
        }

        return $forceLandscape ? $landscapeIssueImagePath : $issueImagePath;
    }

    /**
     * @param Issue[] $issues
     * @param string  $sourceFilePath
     * @param string  $generationTargetFolder
     * @param bool    $forceLandscape
     * @param string  $size
     *
     * @return string|null
     */
    private function generateMapImageInternal(array $issues, string $sourceFilePath, string $generationTargetFolder, $forceLandscape, $size)
    {
        //render pdf to image
        $pdfRenderPath = $this->getRenderedPdfPath($sourceFilePath, $generationTargetFolder);
        if (!$pdfRenderPath) {
            return null;
        }

        if (\count($issues) > 0) {
            //prepare filename for exact issue combination
            $issueToString = function ($issue) {
                /* @var Issue $issue */
                return $issue->getId() . $issue->getStatusCode() . $issue->getLastChangedAt()->format('c');
            };
            $issueHash = hash('sha256', implode(',', array_map($issueToString, $issues)));

            //render issue image
            $issueImagePath = $generationTargetFolder . DIRECTORY_SEPARATOR . $issueHash . '.jpg';
            $landscapeIssueImagePath = $generationTargetFolder . DIRECTORY_SEPARATOR . $issueHash . '_landscape.jpg';
            $issueRenderPath = $this->renderIssues($issues, $pdfRenderPath, $issueImagePath, $landscapeIssueImagePath, $forceLandscape);
        } else {
            // shortcut if no issues to be printed
            $issueRenderPath = $pdfRenderPath;
        }

        return $this->renderSizeVariant($issueRenderPath, $size);
    }

    /**
     * @param string $folderName
     */
    private function ensureFolderExists(string $folderName)
    {
        if (!is_dir($folderName)) {
            mkdir($folderName, 0777, true);
        }
    }

    /**
     * adds sizing infos to filename.
     *
     * @param string $fileName
     * @param string $size
     *
     * @return string
     */
    private function getSizeFilename(string $fileName, $size)
    {
        $ending = pathinfo($fileName, PATHINFO_EXTENSION);
        $filenameWithoutEnding = mb_substr($fileName, 0, -(mb_strlen($ending) + 1));

        return $filenameWithoutEnding . '_' . $size . '.' . $ending;
    }

    /**
     * @param string $sourcePath
     * @param string $targetPath
     * @param int    $maxWidth
     * @param int    $maxHeight
     *
     * @return bool
     */
    private function resizeImage(string $sourcePath, string $targetPath, int $maxWidth, int $maxHeight)
    {
        list($width, $height) = ImageHelper::getWidthHeightArguments($sourcePath, $maxWidth, $maxHeight, false);
        $ending = pathinfo($sourcePath, PATHINFO_EXTENSION);

        //resize & save
        $newImage = imagecreatetruecolor($width, $height);
        if ($ending === 'jpg' || $ending === 'jpeg') {
            $originalImage = imagecreatefromjpeg($sourcePath);
            imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $width, $height, imagesx($originalImage), imagesy($originalImage));
            imagejpeg($newImage, $targetPath, 90);
        } elseif ($ending === 'png') {
            $originalImage = imagecreatefrompng($sourcePath);
            imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $width, $height, imagesx($originalImage), imagesy($originalImage));
            imagepng($newImage, $targetPath, 9);
        } elseif ($ending === 'gif') {
            $originalImage = imagecreatefromgif($sourcePath);
            imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $width, $height, imagesx($originalImage), imagesy($originalImage));
            imagegif($newImage, $targetPath);
        } else {
            return false;
        }

        return true;
    }

    /**
     * @param string $sourceFilePath
     * @param string $generationTargetFolder
     *
     * @return bool|string
     */
    private function getRenderedPdfPath(string $sourceFilePath, string $generationTargetFolder)
    {
        $pdfRenderPath = $generationTargetFolder . DIRECTORY_SEPARATOR . self::MAP_RENDER_NAME;
        if (!file_exists($pdfRenderPath) || $this->disableCache) {
            $this->renderPdfToImage($sourceFilePath, $pdfRenderPath);

            //abort if creation failed
            if (!file_exists($pdfRenderPath)) {
                return false;
            }
        }

        return $pdfRenderPath;
    }

    /**
     * @param string $sourcePath
     * @param string $targetPath
     * @param Frame  $frame
     *
     * @return bool
     */
    private function cropImage(string $sourcePath, string $targetPath, Frame $frame)
    {
        $ending = pathinfo($sourcePath, PATHINFO_EXTENSION);

        $imageSizes = getimagesize($sourcePath);
        $realWidth = $imageSizes[0];
        $realHeight = $imageSizes[1];

        $args = [
            'x' => $frame->startX * $realWidth,
            'y' => $frame->startY * $realHeight,
            'width' => $frame->width * $realWidth,
            'height' => $frame->height * $realHeight,
        ];

        if ($ending === 'jpg' || $ending === 'jpeg') {
            $image = imagecreatefromjpeg($sourcePath);
            imagecrop($image, $args);
            imagejpeg($image, $targetPath, 90);
        } elseif ($ending === 'png') {
            $image = imagecreatefrompng($sourcePath);
            imagecrop($image, $args);
            imagepng($image, $targetPath, 90);
        } elseif ($ending === 'gif') {
            $image = imagecreatefromgif($sourcePath);
            imagecrop($image, $args);
            imagegif($image, $targetPath);
        } else {
            return false;
        }

        return true;
    }

    /**
     * @param string $targetPath
     * @param string $targetSize
     *
     * @return string|null
     */
    private function renderSizeVariant(string $targetPath, string $targetSize)
    {
        $fileName = pathinfo($targetPath, PATHINFO_BASENAME);
        $directory = pathinfo($targetPath, PATHINFO_DIRNAME);

        $targetPathWithSize = $directory . DIRECTORY_SEPARATOR . $this->getSizeFilename($fileName, $targetSize);
        if (!is_file($targetPathWithSize) || $this->disableCache) {
            $this->renderSizeOfImage($targetPath, $targetPathWithSize, $targetSize);

            //abort if creation failed
            if (!is_file($targetPathWithSize)) {
                return null;
            }
        }

        //return the path of the rendered file
        return $targetPathWithSize;
    }
}
