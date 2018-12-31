<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Document\Interfaces;

use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

interface PageLayoutServiceInterface
{
    /**
     * @param PdfDocumentInterface $pdf
     */
    public function initializeLayout(PdfDocumentInterface $pdf);

    /**
     * @param PdfDocumentInterface $pdf
     * @param string $headerLeft
     */
    public function printHeaderLeft(PdfDocumentInterface $pdf, string $headerLeft);

    /**
     * @param PdfDocumentInterface $pdf
     * @param string $logoPath
     */
    public function printLogo(PdfDocumentInterface $pdf, string $logoPath);

    /**
     * @param PdfDocumentInterface $pdf
     * @param string $footerLeft
     */
    public function printFooterLeft(PdfDocumentInterface $pdf, string $footerLeft);

    /**
     * @param PdfDocumentInterface $pdf
     * @param int $currentPageNumber
     * @param int $totalPageNumbers
     */
    public function printPageNumbers(PdfDocumentInterface $pdf, int $currentPageNumber, int $totalPageNumbers);
}
