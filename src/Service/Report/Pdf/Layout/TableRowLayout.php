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

use App\Service\Report\Document\Interfaces\Layout\TableRowLayoutInterface;
use App\Service\Report\Pdf\Cursor;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class TableRowLayout extends ColumnedLayout implements TableRowLayoutInterface
{
    /**
     * ColumnLayout constructor.
     *
     * @param PdfDocumentInterface $pdfDocument
     * @param int $columnCount
     * @param float $columnGutter
     * @param float $totalWidth
     */
    public function __construct(PdfDocumentInterface $pdfDocument, float $columnGutter, float $totalWidth, array $widths)
    {
        parent::__construct($pdfDocument, $columnGutter, $totalWidth, $widths);
    }

    /**
     * register a callable which prints to the pdf document
     * The position of the cursor at the time the callable is invoked is decided by the layout
     * ensure the cursor is below the printed content after the callable is finished to not mess up the layout.
     *
     * @param callable $callable takes a PdfDocumentInterface as first argument and the width as second
     */
    public function registerPrintable(callable $callable)
    {
        // implement grouping
        $callable($this->pdfDocument, $this->columnWidths[$this->activeColumn]);
    }
}
