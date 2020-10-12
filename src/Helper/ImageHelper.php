<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Helper;

class ImageHelper
{
    /**
     * gives back the width and height to be used.
     *
     * @return int[]
     */
    public static function fitInBoundingBox(string $imgPath, int $boxWidth, int $boxHeight, bool $expand = true): array
    {
        //get image sizes
        $imageSizes = getimagesize($imgPath);
        $imageWidth = $imageSizes[0];
        $imageHeight = $imageSizes[1];

        //get ratios
        $widthRatio = (float) $boxWidth / $imageWidth;
        $heightRatio = (float) $boxHeight / $imageHeight;
        $ratio = $widthRatio < $heightRatio ? $widthRatio : $heightRatio;

        if (!$expand) {
            $ratio = min(1, $ratio);
        }

        $newWidth = $imageWidth * $ratio;
        $newHeight = $imageHeight * $ratio;

        return [(int) $newWidth, (int) $newHeight];
    }
}
