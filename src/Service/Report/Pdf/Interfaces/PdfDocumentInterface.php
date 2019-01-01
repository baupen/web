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
     * @param string $text
     * @param float $textSize
     * @param float $width
     * @param bool $alignRight
     */
    public function printText(string $text, float $textSize, float $width = null, bool $alignRight = false);

    /**
     * @param string $text
     * @param float $textSize
     * @param float $width
     * @param bool $alignRight
     */
    public function printBoldText(string $text, float $textSize, float $width = null, bool $alignRight = false);

    /**
     * @param string[] $header
     * @param string[] $content
     */
    public function printTable(array $header, array $content, float $width = null);

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
}
