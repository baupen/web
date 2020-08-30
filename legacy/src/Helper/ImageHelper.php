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
     * @param bool $expand
     *
     * @return array
     */
    public static function getWidthHeightArguments($imgPath, int $maxWidth = null, int $maxHeight = null, $expand = true)
    {
        //get image sizes
        $imageSizes = getimagesize($imgPath);
        $realWidth = $imageSizes[0];
        $realHeight = $imageSizes[1];

        if ($maxWidth === null && $maxHeight === null) {
            $scale = 1;
        } else {
            //get ratios
            $fallbackRatio = $maxHeight === null ? $maxWidth / $realWidth : $maxHeight / $realHeight;
            $widthRatio = $maxWidth !== null ? $maxWidth / $realWidth : $fallbackRatio;
            $heightRatio = $maxHeight !== null ? $maxHeight / $realWidth : $fallbackRatio;

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

            if (!$expand && $scale > 1) {
                $scale = 1;
            }
        }

        return [$realWidth * $scale, $realHeight * $scale];
    }
}
