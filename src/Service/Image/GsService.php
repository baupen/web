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

/**
 * handles ghostscript related stuff.
 *
 * Class GsService
 */
class GsService
{
    public function renderPdfToImage(string $sourcePdfPath, string $targetFilePath)
    {
        //do first low quality render to get artboxsize
        $command = 'gs -sDEVICE=jpeg -dDEVICEWIDTHPOINTS=1920 -dDEVICEHEIGHTPOINTS=1080 -dJPEGQ=1 -dUseCropBox -sPageList=1 -o "'.$targetFilePath.'" "'.$sourcePdfPath.'"';
        exec($command);
        if (!is_file($targetFilePath)) {
            return;
        }

        //second render with correct image dimensions
        list($width, $height) = ImageHelper::fitInBoundingBox($targetFilePath, 3840, 2160);
        $command = 'gs -sDEVICE=jpeg -dDEVICEWIDTHPOINTS='.$width.' -dDEVICEHEIGHTPOINTS='.$height.' -dJPEGQ=80 -dUseCropBox -dFitPage -sPageList=1 -o "'.$targetFilePath.'" "'.$sourcePdfPath.'"';
        exec($command);
    }

    public function renderPdfWithoutOutlineFonts(string $sourcePdfPath, string $targetFilePath)
    {
        $command = ' gs -dNoOutputFonts -sDEVICE=pdfwrite -o "'.$targetFilePath.'" "'.$sourcePdfPath.'"';
        exec($command);
    }
}
