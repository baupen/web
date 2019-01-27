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

interface PageLayoutInterface
{
    /**
     * @param PdfDocumentInterface $pdf
     */
    public function initializeLayout(PdfDocumentInterface $pdf);

    /**
     * @param PdfDocumentInterface $pdf
     */
    public function printHeader(PdfDocumentInterface $pdf);

    /**
     * @param PdfDocumentInterface $pdf
     * @param int $currentPage
     * @param int $totalPages
     */
    public function printFooter(PdfDocumentInterface $pdf, int $currentPage, int $totalPages);
}
