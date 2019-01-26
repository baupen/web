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

use App\Service\Report\Pdf\Tcpdf\Configuration\DrawConfiguration;

interface PdfDocumentDrawInterface extends PdfDocumentCursorInterface
{
    /**
     * @param array $config
     * @param bool $restoreDefaults
     */
    public function configureDraw(array $config = [], bool $restoreDefaults = true);

    /**
     * @return DrawConfiguration
     */
    public function getDrawConfiguration();

    /**
     * @param DrawConfiguration $drawConfiguration
     */
    public function setDrawConfiguration(DrawConfiguration $drawConfiguration);

    /**
     * @param float $height
     * @param float $width
     */
    public function drawArea(float $height, float $width);
}
