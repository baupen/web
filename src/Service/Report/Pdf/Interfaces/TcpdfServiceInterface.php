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

use App\Service\Report\CleanPdf;

interface TcpdfServiceInterface
{
    /**
     * @param CleanPdf $cleanPdf
     */
    public function initialize(CleanPdf $cleanPdf);

    /**
     * @param CleanPdf $pdf
     * @param string $title
     * @param string $logoPath
     */
    public function printHeader(CleanPdf $pdf, string $title, string $logoPath);

    /**
     * @param CleanPdf $pdf
     * @param string $author
     */
    public function printFooter(CleanPdf $pdf, string $author);

    /**
     * @param CleanPdf $cleanPdf
     * @param string $title
     * @param string $author
     */
    public function setMeta(CleanPdf $cleanPdf, string $title, string $author);
}
