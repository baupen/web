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

use App\Service\Report\Pdf\Interfaces\PdfDocument\PdfDocumentPrintInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocument\PdfDocumentStateInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocument\PdfDocumentTransactionInterface;

interface PdfDocumentInterface extends PdfDocumentStateInterface, PdfDocumentPrintInterface, PdfDocumentTransactionInterface
{
    /**
     * @return string
     */
    public function getIdentifier();

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
     * starts a new page & sets the cursor to the next page.
     */
    public function startNewPage();

    /**
     * @param string $filePath
     */
    public function save(string $filePath);
}
