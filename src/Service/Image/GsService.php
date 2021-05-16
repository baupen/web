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
 * handles ghostscript related stuff.
 *
 * Class GsService
 */
class GsService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GsService constructor.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function renderPdfToImage(string $sourcePdfPath, string $targetFilePath): bool
    {
        //do first low quality render to get artboxsize
        $command = 'gs -sDEVICE=jpeg -dDEVICEWIDTHPOINTS=1920 -dDEVICEHEIGHTPOINTS=1080 -dJPEGQ=1 -dUseCropBox -sPageList=1 -o "'.$targetFilePath.'" "'.$sourcePdfPath.'"';
        if (!$this->execute($command)) {
            return false;
        }

        //second render with correct image dimensions
        list($width, $height) = ImageHelper::fitInBoundingBox($targetFilePath, 3840, 2160);
        $command = 'gs -sDEVICE=jpeg -dDEVICEWIDTHPOINTS='.$width.' -dDEVICEHEIGHTPOINTS='.$height.' -dJPEGQ=80 -dUseCropBox -dFitPage -sPageList=1 -o "'.$targetFilePath.'" "'.$sourcePdfPath.'"';
        if (!$this->execute($command)) {
            return false;
        }

        return true;
    }

    public function renderPdfWithoutOutlineFonts(string $sourcePdfPath, string $targetFilePath): bool
    {
        $command = 'gs -dNoOutputFonts -sDEVICE=pdfwrite -o "'.$targetFilePath.'" "'.$sourcePdfPath.'"';

        return $this->execute($command);
    }

    private function execute(string $command): bool
    {
        exec($command, $output, $returnVar);
        if ($returnVar > 0) {
            $this->logger->error('ghostscript execution failed with code '.$returnVar.'. Command: '.$command.'. Output: '.implode("\n", $output));

            return false;
        }

        return true;
    }
}
