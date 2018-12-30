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

use App\Service\Report\Pdf\Pdf;

interface TcpdfServiceInterface
{
    /**
     * @param Pdf $pdf
     * @param string $headerLeft
     * @param string $footerLeft
     * @param string $logoPath
     */
    public function setMeta(Pdf $pdf, string $headerLeft, string $footerLeft, string $logoPath);

    /**
     * @param Pdf $pdf
     */
    public function initialize(Pdf $pdf);

    /**
     * @param Pdf $pdf
     */
    public function printHeader(Pdf $pdf);

    /**
     * @param Pdf $pdf
     */
    public function printFooter(Pdf $pdf);
}
