<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Layout;

use App\Service\Report\Document\Interfaces\Layout\GroupLayoutInterface;
use App\Service\Report\Pdf\Interfaces\CustomPrinterLayoutInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class GroupLayout implements GroupLayoutInterface, CustomPrinterLayoutInterface
{
    /**
     * @var PdfDocumentInterface
     */
    private $pdfDocument;

    /**
     * @var float
     */
    private $width;

    /**
     * @var \Closure[]
     */
    private $buffer = [];

    /**
     * ColumnLayout constructor.
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
     * will end the columned layout.
     */
    public function endLayout()
    {
        $emptyBuffer = function () {
            foreach ($this->buffer as $item) {
                $item();
            }
        };

        if ($this->pdfDocument->causesPageBreak($emptyBuffer)) {
            $this->pdfDocument->startNewPage();
        }

        $emptyBuffer();
    }

    /**
     * register a callable which prints to the pdf document
     * The position of the cursor at the time the callable is invoked is decided by the layout
     * ensure the cursor is below the printed content after the callable is finished to not mess up the layout.
     *
     * @param callable $callable takes a PdfDocumentInterface as an argument
     */
    public function registerPrintable(callable $callable)
    {
        $pdfDocument = $this->pdfDocument;
        $width = $this->width;

        $this->buffer[] = function () use ($callable, $pdfDocument, $width) {
            $callable($pdfDocument, $width);
        };
    }
}
