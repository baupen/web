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
     * gives back the width and height to be used
     * be aware that if passed 0 tcpdf expands to fill the.
     *
     * @param $imgPath
     * @param $maxWidth
     * @param $maxHeight
     *
     * @return array
     */
    public static function getWidthHeightArguments($imgPath, $maxWidth, $maxHeight)
    {
        //get image sizes
        $imageSizes = getimagesize($imgPath);
        $realWidth = $imageSizes[0];
        $realHeight = $imageSizes[1];

        //get ratios
        $widthRatio = $maxWidth / $realWidth;
        $heightRatio = $maxHeight / $realHeight;

        if ($widthRatio < 1 && $heightRatio < 1) {
            //image bigger than box
            if ($widthRatio < $heightRatio) {
                $scale = $widthRatio;
            } else {
                $scale = $heightRatio;
            }
        } elseif ($widthRatio > 1 && $heightRatio > 1) {
            //image smaller than box
            if ($widthRatio > $heightRatio) {
                $scale = $widthRatio;
            } else {
                $scale = $heightRatio;
            }
        } else {
            if ($widthRatio < 1) {
                $scale = $widthRatio;
            } else {
                $scale = $heightRatio;
            }
        }

        return [$realWidth * $scale, $realHeight * $scale];
    }
}
