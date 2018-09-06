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

use App\Entity\Issue;
use App\Entity\Map;
use App\Helper\ImageHelper;
use App\Service\Interfaces\ImageServiceInterface;
use ReflectionClass;

class ImageService implements ImageServiceInterface
{
    /**
     * @var string
     */
    private $pubFolder = __DIR__ . '/../../public';

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
     * @param Map $map
     * @param Issue[] $issues
     * @param string $filePath
     * @param bool $forceLandscape
     *
     * @return string|null
     */
    private function render(Map $map, array $issues, $filePath, $forceLandscape = false): ?string
    {
        //create folder
        $generationTargetFolder = $this->getGenerationTargetFolder($map);
        if (!file_exists($generationTargetFolder)) {
            mkdir($generationTargetFolder, 0777, true);
        }

        //compile pdf to image
        $renderedMapPath = $generationTargetFolder . '/render.jpg';
        if (!is_file($renderedMapPath) || $this->disableCache) {
            $mapFilePath = $this->pubFolder . '/' . $map->getFilePath();

            //do first low quality render to get artboxsize
            $renderedMapPath = $generationTargetFolder . '/pre_render.jpg';
            $command = 'gs -sDEVICE=jpeg -dDEVICEWIDTHPOINTS=1920 -dDEVICEHEIGHTPOINTS=1080 -dJPEGQ=10 -dUseCropBox -sPageList=1 -o ' . $renderedMapPath . ' ' . $mapFilePath;
            exec($command);
            if (!is_file($renderedMapPath)) {
                return null;
            }

            //second render with correct image dimensions
            list($width, $height) = ImageHelper::getWidthHeightArguments($renderedMapPath, 3840, 2160);
            $renderedMapPath = $generationTargetFolder . '/render.jpg';
            $command = 'gs -sDEVICE=jpeg -dDEVICEWIDTHPOINTS=' . $width . ' -dDEVICEHEIGHTPOINTS=' . $height . ' -dJPEGQ=80 -dFitPage -sPageList=1 -o ' . $renderedMapPath . ' ' . $mapFilePath;
            exec($command);
            if (!is_file($renderedMapPath)) {
                return null;
            }
        }

        //open image file
        $sourceImage = imagecreatefromjpeg($renderedMapPath);
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
        imagejpeg($sourceImage, $filePath, 90);
        imagedestroy($sourceImage);

        return is_file($filePath) ? $filePath : null;
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
     * @param Map $map
     * @param array $issues
     * @param string $appendix
     *
     * @return string
     */
    private function getFilePathFor(Map $map, array $issues, $appendix = '')
    {
        //hash issue all used issue info
        $hash = hash('sha256',
            implode(
                ',',
                array_map(
                    function ($issue) {
                        /* @var Issue $issue */
                        return $issue->getId() . $issue->getStatusCode();
                    },
                    $issues)
            ) . $appendix
        );

        return $this->getGenerationTargetFolder($map) . '/' . $hash . '.jpg';
    }

    /**
     * @param Map $map
     *
     * @return string
     */
    private function getGenerationTargetFolder(Map $map)
    {
        return $this->pubFolder . '/generated/' . $map->getConstructionSite()->getId() . '/map/' . $map->getId();
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

        $filePath = $this->getFilePathFor($map, $issues);
        if (!is_file($filePath) || $this->disableCache) {
            $filePath = $this->render($map, $issues, $filePath);
        }

        return $filePath;
    }

    /**
     * @param null|string $imagePath
     * @param string $size
     *
     * @return null|string
     */
    public function getSize(?string $imagePath, $size = ImageServiceInterface::SIZE_THUMBNAIL)
    {
        if ($imagePath !== null && !is_file($imagePath)) {
            return null;
        }

        //get name of variant on disk
        $endingSplitPoint = mb_strrpos($imagePath, '.');
        $path = mb_substr($imagePath, 0, $endingSplitPoint);
        $ending = mb_substr($imagePath, $endingSplitPoint);

        //replace "upload" with "generated" folder
        $pathSplitPoint = mb_strrpos($path, '/upload/');
        if ($pathSplitPoint > 0) {
            $path = mb_substr($path, 0, $pathSplitPoint) . '/generated' . mb_substr($path, $pathSplitPoint + 7);
        }

        //add size & ending
        $path .= '_' . $size . $ending;

        if (!is_file($path) || $this->disableCache) {
            //generate variant if possible
            $res = false;
            switch ($size) {
                case ImageServiceInterface::SIZE_THUMBNAIL:
                    $res = $this->createVariant($imagePath, $path, 100, 50, $ending);
                    break;
                case ImageServiceInterface::SIZE_SHARE_VIEW:
                    $res = $this->createVariant($imagePath, $path, 450, 600, $ending);
                    break;
                case ImageServiceInterface::SIZE_REPORT_ISSUE:
                    $res = $this->createVariant($imagePath, $path, 600, 600, $ending);
                    break;
                case ImageServiceInterface::SIZE_FULL:
                    $res = $this->createVariant($imagePath, $path, 1920, 1080, $ending);
                    break;
                case ImageServiceInterface::SIZE_REPORT_MAP:
                    $res = $this->createVariant($imagePath, $path, 2480, 2480, $ending);
                    break;
            }

            //check is successful
            if (!$res || !is_file($path)) {
                return null;
            }
        }

        return $path;
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
        $folder = dirname($targetPath);
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
     * @param null|string $imagePath
     */
    public function warmupCache(?string $imagePath)
    {
        try {
            $oClass = new ReflectionClass(ImageServiceInterface::class);
            foreach ($oClass->getConstants() as $name => $value) {
                if (mb_strpos($name, 'SIZE') === 0) {
                    $this->getSize($imagePath, $value);
                }
            }
        } catch (\ReflectionException $e) {
            //this will not fail because the ImageServiceInterface is well defined
        }
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

        $filePath = $this->getFilePathFor($map, $issues, 'landscape');
        if (!is_file($filePath) || $this->disableCache) {
            $filePath = $this->render($map, $issues, $filePath, true);
        }

        return $filePath;
    }
}
