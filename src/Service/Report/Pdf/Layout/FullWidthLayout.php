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

use App\Service\Report\Document\Interfaces\Layout\FullWidthLayoutInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class FullWidthLayout implements FullWidthLayoutInterface
{
    /**
     * @var float
     */
    private $width;

    /**
     * @var PdfDocumentInterface
     */
    private $pdfDocument;

    /**
     * ColumnLayout constructor.
     *
     * @param float $width
     */
    public function __construct(PdfDocumentInterface $pdfDocument, float $width)
    {
        $this->width = $width;
        $this->pdfDocument = $pdfDocument;
    }

    /**
     * will end the columned layout.
     */
    public function endLayout()
    {
        // no need to act here
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
        $callable($this->pdfDocument, $this->width);
    }
}
