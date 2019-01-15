<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Interfaces\PdfDocument;

interface PdfDocumentPrintInterface
{
    /**
     * @param array $config
     * @param bool $restoreDefaults
     */
    public function configurePrint(array $config = [], bool $restoreDefaults = true);

    /**
     * @return object
     */
    public function getPrintConfiguration();

    /**
     * @param $printConfiguration
     */
    public function setPrintConfiguration($printConfiguration);

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
}
