<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Interfaces;

interface PdfDocumentInterface extends ReadOnlyPdfDocumentInterface
{
    /**
     * @param string $title
     * @param string $author
     */
    public function setMeta(string $title, string $author);

    /**
     * @param float $marginLeft
     * @param float $marginTop
     * @param float $marginRight
     * @param float $marginBottom
     */
    public function setPageMargins(float $marginLeft, float $marginTop, float $marginRight, float $marginBottom);

    /**
     * @param float $xCoordinate
     * @param float $yCoordinate
     */
    public function setCursor(float $xCoordinate, float $yCoordinate);

    /**
     * @param int $page
     */
    public function setPage(int $page);

    /**
     * @param array $config
     * @param bool $restoreDefaults
     */
    public function configurePrint(array $config = [], bool $restoreDefaults = true);

    /**
     * @param string $text
     */
    public function printText(string $text, float $width);

    /**
     * @param string $imagePath
     * @param float $width
     * @param float $height
     */
    public function printImage(string $imagePath, float $width, float $height);

    /**
     * @param string $filePath
     */
    public function save(string $filePath);

    /**
     * @param \Closure $printClosure
     *
     * @return bool
     */
    public function provocatesPageBreak(\Closure $printClosure);

    /**
     * starts a new page & sets the cursor to the next page.
     */
    public function startNewPage();
}
