<?php

/*
 * This file is part of the baupen project.
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
    private LoggerInterface $logger;

    private const FONT = __DIR__.'/../../../assets/report/fonts/OpenSans-Bold.ttf';

    /**
     * GdService constructor.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function measureTextDimensions(float $fontSize, string $text)
    {
        // get text dimensions
        $boundingBox = imagettfbbox($fontSize, 0, self::FONT, $text);
        $textWidth = abs($boundingBox[4] - $boundingBox[0]);
        $textHeight = abs($boundingBox[5] - $boundingBox[1]);

        return [$textWidth, $textHeight];
    }

    /**
     * @param resource|\GdImage $image
     */
    public function drawBorderedRectangleWithTextCentered(float $xPosition, float $yPosition, string $color, float $padding, string $text, float $textFontSize, float $textWidth, float $textHeight, &$image)
    {
        // draw white base ellipse before the colored one
        $white = $this->createColorForLabel('white', $image);
        $fillColor = $this->createColorForLabel($color, $image);
        $halfHeight = $textHeight / 2;
        $textStart = $xPosition - ($textWidth / 2);
        $textEnd = $xPosition + ($textWidth / 2);
        imagerectangle($image, (int) ($textStart - $padding - 1), (int) ($yPosition - $halfHeight - $padding - 1), (int) ($textEnd + $padding + 1), (int) ($yPosition + $halfHeight + $padding + 1), $white);
        imagefilledrectangle($image, (int) ($textStart - $padding), (int) ($yPosition - $padding - $halfHeight), (int) ($textEnd + $padding), (int) ($yPosition + $halfHeight + $padding), $fillColor);

        // draw text
        imagettftext($image, $textFontSize, 0, (int) $textStart, (int) ($yPosition + $halfHeight), $white, self::FONT, $text);
    }

    /**
     * @param resource|\GdImage $image
     */
    public function drawRectangleWithText(float $xPosition, float $yPosition, string $color, float $padding, string $text, float $textFontSize, float $textWidth, float $textHeight, &$image)
    {
        // draw white base ellipse before the colored one
        $white = $this->createColorForLabel('white', $image);
        $fillColor = $this->createColorForLabel($color, $image);
        $width = $textWidth + 2 * $padding + 2;
        $height = $textHeight + 2 * $padding + 2;
        imagefilledrectangle($image, (int) $xPosition, (int) $yPosition, (int) $xPosition + $width, (int) $yPosition + $height, $fillColor);

        // draw text
        imagettftext($image, $textFontSize, 0, (int) ($xPosition + $padding), (int) ($yPosition + $padding + $textHeight), $white, self::FONT, $text);
    }

    /**
     * @param resource|\GdImage $image
     */
    public function drawCrosshair(float $positionX, float $positionY, string $color, int $radius, int $circleThickness, int $lineThickness, &$image)
    {
        $accent = $this->createColorForLabel($color, $image);
        $transparentAccent = $this->createColorForLabel($color, $image, 0.2);

        // image thickness + arcs do not work well: https://stackoverflow.com/a/37974450/2259391
        imagesetthickness($image, 2);
        $diameter = $radius * 2;
        for ($i = $circleThickness - 1; $i > 1; --$i) {
            imagearc($image, (int) $positionX, (int) $positionY, $diameter - $i, $diameter - $i, 0, 360, $accent);
        }
        imagearc($image, (int) $positionX, (int) $positionY, $diameter - $circleThickness, $diameter - $circleThickness, 0, 360, $transparentAccent);
        imagearc($image, (int) $positionX, (int) $positionY, $diameter, $diameter, 0, 360, $transparentAccent);

        if ($lineThickness > 0) {
            imagesetthickness($image, $lineThickness);

            // draw only until middle of circle
            $lineRadius = (int) ($radius - $circleThickness / 2);
            imageline($image, (int) ($positionX + $lineRadius), (int) $positionY, (int) ($positionX - $lineRadius), (int) $positionY, $accent);
            imageline($image, (int) $positionX, (int) ($positionY + $lineRadius), (int) $positionX, (int) ($positionY - $lineRadius), $accent);
        }
    }

    public function resizeImage(string $sourcePath, string $targetPath, int $maxWidth, int $maxHeight): bool
    {
        list($width, $height) = ImageHelper::fitInBoundingBox($sourcePath, $maxWidth, $maxHeight, false);
        $ending = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        // resize & save
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
            imagejpeg($newImage, $targetPath, 80);
        } elseif ('png' === $ending) {
            $originalImage = imagecreatefrompng($sourcePath);
            if (!$originalImage) {
                return false;
            }

            imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $width, $height, imagesx($originalImage), imagesy($originalImage));
            imagepng($newImage, $targetPath, 8);
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
     * @param \GdImage|resource $image
     *
     * @return int|false
     *
     * @throws \Exception
     */
    private function createColorForLabel(string $label, &$image, float $opacity = 1)
    {
        $alpha = (int) ((1 - $opacity) * 127);
        switch ($label) {
            case 'gray':
                return $this->createColor($image, 18, 18, 18, $alpha);
            case 'green':
                return $this->createColor($image, 18, 140, 45, $alpha);
            case 'orange':
                return $this->createColor($image, 201, 151, 0, $alpha);
            case 'blue':
                return $this->createColor($image, 52, 52, 119, $alpha);
            case 'white':
                return $this->createColor($image, 255, 255, 255, $alpha);
            default:
                throw new \Exception('Unknown color');
        }
    }

    /**
     * create a color using the palette of the image.
     *
     * @param resource|\GdImage $image
     *
     * @return int|false
     */
    private function createColor($image, int $red, int $green, int $blue, int $alpha = 0)
    {
        // get color from palette
        $color = imagecolorexactalpha($image, $red, $green, $blue, $alpha);
        if (-1 === $color) {
            // color does not exist...
            // test if we have used up palette
            if (imagecolorstotal($image) >= 255) {
                // palette used up; pick the closest assigned color
                $color = imagecolorclosestalpha($image, $red, $green, $blue, $alpha);
            } else {
                // palette NOT used up; assign new color
                $color = imagecolorallocatealpha($image, $red, $green, $blue, $alpha);
            }
        }

        return $color;
    }
}
