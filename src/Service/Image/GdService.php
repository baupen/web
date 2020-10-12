<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Image;

use App\Helper\ImageHelper;
use Psr\Log\LoggerInterface;

/**
 * handles image related stuff.
 *
 * Class GdService
 */
class GdService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int the bubble size as an abstract unit
     *          the higher the number the smaller the resulting bubble
     */
    private $bubbleScale = 800;

    /**
     * @param float $yPosition
     * @param float $xPosition
     * @param $circleColor
     * @param $text
     * @param $image
     */
    public function drawRectangleWithText($yPosition, $xPosition, $circleColor, $text, &$image)
    {
        $textFactor = mb_strlen($text) / 2.6;

        //get sizes
        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $imageSize = $xSize * $ySize;
        $targetTextDimension = sqrt($imageSize / ($this->bubbleScale * M_PI)) * $textFactor;

        //get text dimensions
        $font = __DIR__.'/../../assets/fonts/OpenSans-Bold.ttf';
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

        //draw white base ellipse before the colored one
        $white = $this->createColor($image, 255, 255, 255);
        $fillColor = 'green' == $circleColor ? $this->createColor($image, 18, 140, 45) : $this->createColor($image, 201, 151, 0);
        $padding = $textHeight * 0.3;
        $halfHeight = $textHeight / 2;
        $textStart = $xPosition - ($textWidth / 2);
        $textEnd = $xPosition + ($textWidth / 2);
        imagerectangle($image, (int) ($textStart - $padding - 1), (int) ($yPosition - $halfHeight - $padding - 1), (int) ($textEnd + $padding + 1), (int) ($yPosition + $halfHeight + $padding + 1), $white);
        imagefilledrectangle($image, (int) ($textStart - $padding), (int) ($yPosition - $padding - $halfHeight), (int) ($textEnd + $padding), (int) ($yPosition + $halfHeight + $padding), $fillColor);

        //draw text
        imagettftext($image, $fontSize, 0, (int) ($textStart), (int) ($yPosition + $halfHeight), $white, $font, $text);
    }

    public function resizeImage(string $sourcePath, string $targetPath, int $maxWidth, int $maxHeight): bool
    {
        list($width, $height) = ImageHelper::fitInBoundingBox($sourcePath, $maxWidth, $maxHeight, false);
        $ending = pathinfo($sourcePath, PATHINFO_EXTENSION);

        //resize & save
        $newImage = imagecreatetruecolor($width, $height);
        if (!$newImage) {
            return false;
        }

        if ('jpg' === $ending || 'jpeg' === $ending) {
            $originalImage = imagecreatefromjpeg($sourcePath);
            if (!$originalImage) {
                return false;
            }

            imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $width, $height, imagesx($originalImage), imagesy($originalImage));
            imagejpeg($newImage, $targetPath, 90);
        } elseif ('png' === $ending) {
            $originalImage = imagecreatefrompng($sourcePath);
            if (!$originalImage) {
                return false;
            }

            imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $width, $height, imagesx($originalImage), imagesy($originalImage));
            imagepng($newImage, $targetPath, 9);
        } elseif ('gif' === $ending) {
            $originalImage = imagecreatefromgif($sourcePath);
            if (!$originalImage) {
                return false;
            }

            imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $width, $height, imagesx($originalImage), imagesy($originalImage));
            imagegif($newImage, $targetPath);
        } else {
            $this->logger->warning('cannot resize image with ending '.$ending);
            // can not resize; but at least create the file
            copy($sourcePath, $targetPath);
        }

        return true;
    }

    /**
     * create a color using the palette of the image.
     *
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
        if (-1 === $color) {
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
}
