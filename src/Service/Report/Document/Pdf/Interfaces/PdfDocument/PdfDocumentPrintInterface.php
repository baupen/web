<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Document\Pdf\Interfaces\PdfDocument;

use App\Service\Report\Document\Pdf\Cursor;

interface PdfDocumentPrintInterface
{
    /**
     * @param string $text
     * @param float $width
     */
    public function printText(string $text, float $width);

    /**
     * @param string $imagePath
     * @param float $width
     * @param float $height
     */
    public function printImage(string $imagePath, float $width, float $height);

    /**
     * @param Cursor $target
     */
    public function drawUntil(Cursor $target);
}
