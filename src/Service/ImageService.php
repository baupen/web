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
use App\Helper\ImageHelper;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use ReflectionClass;
use function Sodium\library_version_major;

class ImageService implements ImageServiceInterface
{
    // TODO: unit test to detect all enums in here
    /**
     * @var array
     */
    private $validSizes = [ImageServiceInterface::SIZE_FULL, ImageServiceInterface::SIZE_REPORT_ISSUE, ImageServiceInterface::SIZE_REPORT_MAP, ImageServiceInterface::SIZE_SHARE_VIEW, ImageServiceInterface::SIZE_THUMBNAIL];

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

    public function __construct(PathServiceInterface $pathService)
    {
        $this->pathService = $pathService;
    }

    /**
     * @param string $sourcePdfPath
     * @param string $targetFilepath
     * @return string|null
     */
    private function renderPdfToImage(string $sourcePdfPath, string $targetFilepath)
    {
        //compile pdf to image
        if (!is_file($targetFilepath) || $this->disableCache) {
            // setup paths
            list($targetFolder, $targetFilename) = $this->decomposePath($targetFilepath);
            list($filenameWithoutEnding, $ending) = $this->decomposeFilename($targetFilename);


            //do first low quality render to get artboxsize
            $renderedMapPath = $targetFolder . DIRECTORY_SEPARATOR . $filenameWithoutEnding . "_pre_render" . $ending;
            $command = 'gs -sDEVICE=jpeg -dDEVICEWIDTHPOINTS=1920 -dDEVICEHEIGHTPOINTS=1080 -dJPEGQ=10 -dUseCropBox -sPageList=1 -o ' . $renderedMapPath . ' ' . $sourcePdfPath;
            exec($command);
            if (!is_file($renderedMapPath)) {
                return null;
            }

            //second render with correct image dimensions
            list($width, $height) = ImageHelper::getWidthHeightArguments($renderedMapPath, 3840, 2160);
            $renderedMapPath = $targetFolder . DIRECTORY_SEPARATOR  . $targetFilepath;
            $command = 'gs -sDEVICE=jpeg -dDEVICEWIDTHPOINTS=' . $width . ' -dDEVICEHEIGHTPOINTS=' . $height . ' -dJPEGQ=80 -dFitPage -sPageList=1 -o ' . $renderedMapPath . ' ' . $sourcePdfPath;
            exec($command);
            if (!is_file($renderedMapPath)) {
                return null;
            }
        }

        return $renderedMapPath;
    }

    /**
     * @param Issue[] $issues
     * @param string $mapImagePath
     * @param string $targetPath
     * @param bool $forceLandscape
     *
     * @return string|null
     */
    private function renderIssues(array $issues, string $mapImagePath, string $targetPath, $forceLandscape = false): ?string
    {
        //open image file
        $sourceImage = imagecreatefromjpeg($mapImagePath);
        $rotated = false;

        if ($forceLandscape) {
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);

            if ($height > $width) {
                $rotated = true;
                $sourceImage = imagerotate($sourceImage, 90, 0);
            }
        }

        //draw the issues on the map
        foreach ($issues as $issue) {
            if ($issue->getPositionX() !== null) {
                $this->draw($issue, $rotated, $sourceImage);
            }
        }

        //write to disk & destroy
        imagejpeg($sourceImage, $targetPath, 90);
        imagedestroy($sourceImage);

        return is_file($targetPath) ? $targetPath : null;
    }

    /**
     * @param Issue $issue
     * @param bool $rotated
     * @param $image
     */
    private function draw(Issue $issue, bool $rotated, &$image)
    {
        //get sizes
        $xSize = imagesx($image);
        $ySize = imagesy($image);

        //target location
        if ($rotated) {
            $yCoordinate = $issue->getPositionX();
            $xCoordinate = $issue->getPositionY();
        } else {
            $yCoordinate = $issue->getPositionY();
            $xCoordinate = $issue->getPositionX();
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

        $this->drawCircleWithText($yCoordinate, $xCoordinate, $circleColor, (string)$issue->getNumber(), $image);
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
        imagefilledellipse($image, (int)$xPosition, (int)$yPosition, (int)($diameter + 2), (int)($diameter + 2), $white);
        imagefilledellipse($image, (int)$xPosition, (int)$yPosition, (int)$diameter, (int)$diameter, $circleColor);

        //draw text
        imagettftext($image, $fontSize, 0, (int)($xPosition - ($textWidth / 2)), (int)($yPosition + ($textHeight / 2)), $white, $font, $text);
    }

    /**
     * @param resource $image
     * @param int $red
     * @param int $green
     * @param int $blue
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
     * @param array $issues
     * @param string $appendix
     *
     * @return string
     */
    private function getStatefulHash(array $issues, $appendix = '')
    {
        //hash issue all used issue info
        return hash('sha256',
            implode(
                ',',
                array_map(
                    function ($issue) {
                        /* @var Issue $issue */
                        return $issue->getId() . $issue->getStatusCode() . $issue->getLastChangedAt()->format("c");
                    },
                    $issues)
            ) . $appendix
        );
    }

    /**
     * @param Map $map
     * @param array $issues
     *
     * @return string
     */
    public function generateMapImage(Map $map, array $issues)
    {
        if ($map->getFilename() === null) {
            return null;
        }

        //create folder
        $sourceFilePath = $this->pathService->getFolderForMap($map->getConstructionSite());
        $generationTargetFolder = $this->pathService->getTransientFolderForMap($map);
        $this->ensureFolderExists($generationTargetFolder);

        //TODO confinue here



        $renderedMapPath = $this->renderPdfToImage($sourceFilePath)

        $filePath = $this->getStatefulHash($map, $issues);
        if (!is_file($filePath) || $this->disableCache) {
            $filePath = $this->renderIssues($map, $issues, $filePath);
        }

        return $filePath;
    }

    private function ensureFolderExists(string $folderName)
    {
        if (!is_dir($folderName)) {
            mkdir($folderName, 0777, true);
        }
    }

    /**
     * @param string $filename
     * @return array
     */
    private function decomposeFilename(string $filename)
    {
        $endingIndex = strrpos($filename, ".");
        $ending = substr($filename, $endingIndex);
        $filenameWithoutEnding = substr($filename, 0, $endingIndex);

        return [$ending, $filenameWithoutEnding];
    }


    /**
     * @param string $filename
     * @return array
     */
    private function decomposePath(string $filename)
    {
        $directorySeparatorIndex = strrpos($filename, DIRECTORY_SEPARATOR);
        $fileName = substr($filename, $directorySeparatorIndex + 1);
        $folder = substr($filename, 0, $directorySeparatorIndex);

        return [$folder, $fileName];
    }

    /**
     * @param string $sourceFolder
     * @param string $sourceFilename
     * @param string $targetFolder
     * @param string $size
     *
     * @return null|string
     */
    private function renderSize(string $sourceFolder, string $sourceFilename, string $targetFolder, $size = ImageServiceInterface::SIZE_THUMBNAIL)
    {
        // decompose filename
        list($filenameWithoutEnding, $ending) = $this->decomposeFilename($sourceFilename);

        // setup paths
        $sourceFilePath = $sourceFolder . DIRECTORY_SEPARATOR . $sourceFilename;
        $targetFilePath = $targetFolder . DIRECTORY_SEPARATOR . $filenameWithoutEnding . "_" . $size . $ending;
        //TODO: take care of file hash

        if (!is_file($sourceFilePath)) {
            return null;
        }

        if (!is_file($targetFilePath) || $this->disableCache) {
            //generate variant if possible
            $res = false;
            switch ($size) {
                case ImageServiceInterface::SIZE_THUMBNAIL:
                    $res = $this->createVariant($sourceFilePath, $targetFilePath, 100, 50, $ending);
                    break;
                case ImageServiceInterface::SIZE_SHARE_VIEW:
                    $res = $this->createVariant($sourceFilePath, $targetFilePath, 450, 600, $ending);
                    break;
                case ImageServiceInterface::SIZE_REPORT_ISSUE:
                    $res = $this->createVariant($sourceFilePath, $targetFilePath, 600, 600, $ending);
                    break;
                case ImageServiceInterface::SIZE_FULL:
                    $res = $this->createVariant($sourceFilePath, $targetFilePath, 1920, 1080, $ending);
                    break;
                case ImageServiceInterface::SIZE_REPORT_MAP:
                    $res = $this->createVariant($sourceFilePath, $targetFilePath, 2480, 2480, $ending);
                    break;
            }

            //check if successful
            if (!$res || !is_file($targetFilePath)) {
                return null;
            }
        }

        return $targetFilePath;
    }

    /**
     * @param string $sourcePath
     * @param string $targetPath
     * @param int $maxWidth
     * @param int $maxHeight
     * @param string $ending
     *
     * @return bool
     */
    private function createVariant(string $sourcePath, string $targetPath, int $maxWidth, int $maxHeight, string $ending)
    {
        list($width, $height) = ImageHelper::getWidthHeightArguments($sourcePath, $maxWidth, $maxHeight, false);

        //create folder if needed
        $folder = \dirname($targetPath);
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        //resize & save
        $newImage = imagecreatetruecolor($width, $height);
        if ($ending === '.jpg' || $ending === '.jpeg') {
            $originalImage = imagecreatefromjpeg($sourcePath);
            imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $width, $height, imagesx($originalImage), imagesy($originalImage));
            imagejpeg($newImage, $targetPath, 90);
        } elseif ($ending === '.png') {
            $originalImage = imagecreatefrompng($sourcePath);
            imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $width, $height, imagesx($originalImage), imagesy($originalImage));
            imagepng($newImage, $targetPath, 9);
        } elseif ($ending === '.gif') {
            $originalImage = imagecreatefromgif($sourcePath);
            imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $width, $height, imagesx($originalImage), imagesy($originalImage));
            imagegif($newImage, $targetPath);
        } else {
            return false;
        }

        return true;
    }

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     *
     * @param Issue $issue
     */
    public function warmupCacheForIssue(Issue $issue)
    {
        foreach ($this->validSizes as $validSize) {
            $this->getSizeForIssue($issue, $validSize);
        }
    }

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     *
     * @param ConstructionSite $constructionSite
     */
    public function warmupCacheForConstructionSite(ConstructionSite $constructionSite)
    {
        foreach ($this->validSizes as $validSize) {
            $this->getSizeForConstructionSite($constructionSite, $validSize);
        }
    }

    /**
     * @param string $uncheckedSize
     * @return string
     */
    public function ensureValidSize($uncheckedSize)
    {
        return in_array($uncheckedSize, $this->validSizes) ? $uncheckedSize : ImageServiceInterface::SIZE_THUMBNAIL;
    }

    /**
     * @param Issue $issue
     * @param string $size
     *
     * @return string|null
     */
    public function getSizeForIssue(Issue $issue, $size = self::SIZE_THUMBNAIL)
    {
        if ($issue->getImageFilename() === null) {
            return null;
        }

        $sourceFolder = $this->pathService->getFolderForIssue($issue->getMap()->getConstructionSite());
        $targetFolder = $this->pathService->getTransientFolderForIssue($issue);
        return $this->renderSize($sourceFolder, $issue->getImageFilename(), $targetFolder, $size);
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param string $size
     *
     * @return string|null
     */
    public function getSizeForConstructionSite(ConstructionSite $constructionSite, $size = self::SIZE_THUMBNAIL)
    {
        if ($constructionSite->getImageFilename() === null) {
            return null;
        }

        $sourceFolder = $this->pathService->getFolderForConstructionSite($constructionSite);
        $targetFolder = $this->pathService->getTransientFolderForConstructionSite($constructionSite);
        return $this->renderSize($sourceFolder, $constructionSite->getImageFilename(), $targetFolder, $size);
    }

    /**
     * generates all sizes so the getSize call goes faster once it is really needed.
     *
     * @param Map $map
     */
    public function warmupCacheForMap(Map $map)
    {
        // TODO: Implement warmupCacheForMap() method.
    }

    /**
     * @param Map $map
     * @param array $issues
     *
     * @return string
     */
    public function generateMapImageForReport(Map $map, array $issues)
    {
        if ($map->getFilename() === null) {
            return null;
        }

        $filePath = $this->getStatefulHash($map, $issues, 'landscape');
        if (!is_file($filePath) || $this->disableCache) {
            $filePath = $this->renderIssues($map, $issues, $filePath, true);
        }

        return $filePath;
    }
}
