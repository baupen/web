<?php

namespace App\Helper;

class ImageHelper
{
    /**
     * gives back the width and height to be used.
     *
     * @return int[]
     */
    public static function fitInBoundingBox(string $imgPath, mixed $boxWidth, mixed $boxHeight, bool $expand = true): array
    {
        // get image sizes
        $imageSizes = getimagesize($imgPath);
        $imageWidth = $imageSizes[0];
        $imageHeight = $imageSizes[1];

        return self::fitInBoundingBoxRaw($imageWidth, $imageHeight, $boxWidth, $boxHeight, $expand);
    }

    /**
     * gives back the width and height to be used.
     *
     * @return int[]
     */
    public static function fitInBoundingBoxRaw(int $imageWidth, int $imageHeight, mixed $boxWidth, mixed $boxHeight, bool $expand = true): array
    {
        // get ratios
        $widthRatio = (float) $boxWidth / $imageWidth;
        $heightRatio = (float) $boxHeight / $imageHeight;
        $ratio = min($widthRatio, $heightRatio);

        if (!$expand) {
            $ratio = min(1, $ratio);
        }

        $newWidth = $imageWidth * $ratio;
        $newHeight = $imageHeight * $ratio;

        return [(int) $newWidth, (int) $newHeight];
    }
}
