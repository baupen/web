<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Layout\Supporting;

use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class PrintBuffer
{
    /**
     * @var callable[]
     */
    private $printBuffer;

    /**
     * @var PdfDocumentInterface
     */
    private $pdfDocument;

    /**
     * @var float
     */
    private $width;

    /**
     * PrintBuffer constructor.
     *
     * @param PdfDocumentInterface $pdfDocument
     * @param float $width
     */
    public function __construct(PdfDocumentInterface $pdfDocument, float $width)
    {
        $this->pdfDocument = $pdfDocument;
        $this->width = $width;
    }

    /**
     * @param callable $callable
     * @param callable $setCursor
     * @param null $widthOverride
     */
    public function addPrintable(callable $callable, callable $setCursor = null, $widthOverride = null)
    {
        $pdfDocument = $this->pdfDocument;
        $width = $widthOverride === null ? $this->width : $widthOverride;

        $printConfig = $pdfDocument->getPrintConfiguration();

        $this->printBuffer[] = function () use ($pdfDocument, $width, $setCursor, $callable, $printConfig) {
            $pdfDocument->setPrintConfiguration($printConfig);

            if (\is_callable($setCursor)) {
                $width = $setCursor($pdfDocument);
            }

            $callable($pdfDocument, $width);
        };
    }

    /**
     * @return \Closure
     */
    public function flushBufferClosure(): \Closure
    {
        return function () {
            foreach ($this->printBuffer as $item) {
                $item();
            }
        };
    }
}
