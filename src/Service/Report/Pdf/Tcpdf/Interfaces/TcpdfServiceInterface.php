<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Tcpdf\Interfaces;

use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Tcpdf\Pdf;

interface TcpdfServiceInterface
{
    /**
     * @param Pdf $pdf
     * @param PdfDocumentInterface $document
     */
    public function assignWrapper(Pdf $pdf, PdfDocumentInterface $document);

    /**
     * @param Pdf $document
     * @param string $headerLeft
     * @param string $footerLeft
     * @param string $logoPath
     */
    public function setPageVariables(Pdf $document, string $headerLeft, string $footerLeft, string $logoPath);

    /**
     * @param Pdf $pdf
     */
    public function printHeader(Pdf $pdf);

    /**
     * @param Pdf $pdf
     * @param int $currentPageNumber
     * @param int $totalPageNumbers
     */
    public function printFooter(Pdf $pdf, int $currentPageNumber, int $totalPageNumbers);

    /**
     * sets the global variables needed for TCPDF.
     */
    public function initializeGlobalVariables();
}
