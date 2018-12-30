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

use App\Service\Report\Pdf\Pdf;

interface PrintMetaServiceInterface
{
    /**
     * @param Pdf $pdf
     */
    public function initializeLayout(Pdf $pdf);

    /**
     * @param Pdf $pdf
     * @param string $headerLeft
     */
    public function printHeaderLeft(Pdf $pdf, string $headerLeft);

    /**
     * @param Pdf $pdf
     * @param string $logoPath
     */
    public function printLogo(Pdf $pdf, string $logoPath);

    /**
     * @param Pdf $pdf
     * @param string $footerLeft
     */
    public function printFooterLeft(Pdf $pdf, string $footerLeft);

    /**
     * @param Pdf $pdf
     */
    public function printPageNumbers(Pdf $pdf);
}
