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

interface PrintableProducerInterface
{
    /**
     * sets the printer to be used. Invoked as soon as an instance is passed to a layout.
     *
     * @param PdfLayoutInterface $printer
     */
    public function setPdfPrinter(PdfLayoutInterface $printer);
}
