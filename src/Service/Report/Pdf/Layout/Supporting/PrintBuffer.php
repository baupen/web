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
     * @param float|null $widthOverride
     */
    public function prependPrintable(callable $callable, callable $setCursor = null, float $widthOverride = null)
    {
        $this->printBuffer = array_merge([$this->getPrintBufferEntry($callable, $setCursor, $widthOverride)], $this->printBuffer);
    }

    /**
     * @param callable $callable
     * @param callable $setCursor
     * @param float|null $widthOverride
     */
    public function addPrintable(callable $callable, callable $setCursor = null, float $widthOverride = null)
    {
        $this->printBuffer[] = $this->getPrintBufferEntry($callable, $setCursor, $widthOverride);
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

    /**
     * @param PrintBuffer $buffer
     *
     * @return PrintBuffer
     */
    public static function createFromExisting(self $buffer)
    {
        $newBuffer = new self($buffer->pdfDocument, $buffer->width);
        $newBuffer->printBuffer = $buffer->printBuffer;

        return $newBuffer;
    }

    /**
     * @param callable $callable
     * @param callable $setCursor
     * @param float|null $widthOverride
     *
     * @return \Closure
     */
    private function getPrintBufferEntry(callable $callable, callable $setCursor = null, $widthOverride = null)
    {
        $pdfDocument = $this->pdfDocument;
        $width = $widthOverride === null ? $this->width : $widthOverride;

        $printConfig = $pdfDocument->getPrintConfiguration();

        return function () use ($pdfDocument, $width, $setCursor, $callable, $printConfig) {
            $pdfDocument->setPrintConfiguration($printConfig);

            if (\is_callable($setCursor)) {
                $width = $setCursor($pdfDocument);
            }

            $callable($pdfDocument, $width);
        };
    }
}
