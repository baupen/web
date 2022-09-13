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

    public function renderPdfToImage(string $sourcePdfPath, string $targetFilePath, int $maxWidth, int $maxHeight): bool
    {
        // do first low quality render (quality = 1, dpi = 20) to get cropbox size
        $dpi = 20;
        $command = 'gs -sDEVICE=jpeg -dJPEGQ=1 -r'.$dpi.' -dUseCropBox -sPageList=1 -o "'.$targetFilePath.'" "'.$sourcePdfPath.'"';
        if (!$this->execute($command)) {
            return false;
        }

        // render again tweaking DPI to get expected image size
        // we do not use -dFitPage as it failed to correctly rotate pages in GPL Ghostscript 9.56.1 (2022-04-04)
        $newDpi = $this->calculateTargetDpi($targetFilePath, $dpi, $maxWidth, $maxHeight);
        $command = 'gs -sDEVICE=jpeg -dJPEGQ=80 -r'.$newDpi.' -dUseCropBox -sPageList=1  -o "'.$targetFilePath.'" "'.$sourcePdfPath.'"';
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

    public function calculateTargetDpi(string $targetFilePath, int $dpi, int $maxWidth, int $maxHeight): ?int
    {
        $imageSize = getimagesize($targetFilePath);
        if (!$imageSize) {
            return $dpi;
        }

        [$imageWidth, $imageHeight] = $imageSize;

        // prevent extreme relations to blow up the DPI
        $imageWidth = min(3 * $imageHeight, $imageWidth);
        $imageHeight = min(3 * $imageWidth, $imageHeight);

        $xDpi = (float) $maxWidth / $imageWidth * $dpi;
        $yDpi = (float) $maxHeight / $imageHeight * $dpi;

        return min((int) $xDpi, (int) $yDpi);
    }
}
