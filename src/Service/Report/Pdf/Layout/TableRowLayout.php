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
use App\Service\Report\Pdf\Layout\Supporting\PrintBuffer;

class TableRowLayout extends ColumnedLayout implements TableRowLayoutInterface
{
    /**
     * @var callable[]
     */
    private $buffer;

    /**
     * @var PdfDocumentInterface
     */
    private $pdfDocument;

    /**
     * @var float
     */
    private $fullWidth;

    /**
     * @var PrintBuffer
     */
    private $printBuffer;

    /**
     * ColumnLayout constructor.
     *
     * @param PdfDocumentInterface $pdfDocument
     * @param float $columnGutter
     * @param float $totalWidth
     * @param array $widths
     */
    public function __construct(PdfDocumentInterface $pdfDocument, float $columnGutter, float $totalWidth, array $widths)
    {
        parent::__construct($pdfDocument, $columnGutter, $totalWidth, $widths);

        $this->pdfDocument = $pdfDocument;
        $this->fullWidth = $totalWidth;
        $this->printBuffer = new PrintBuffer($pdfDocument);
    }

    /**
     * register a callable which prints to the pdf document
     * The position of the cursor at the time the callable is invoked is decided by the layout
     * after the callable terminates, the cursor is reset to the position it was before.
     *
     * @param callable $callable takes a PdfDocumentInterface as first argument, width as the second and height as the third
     */
    public function printBackground(callable $callable)
    {
        /**
         * TODO: we need a PrintTransaction which encouples this functionality
         * returned upon endLayout, it contains information about the to-be-printed areas like start/end/size
         * and the option to commit the transaction
         * can directly use the print buffer renamed to print transaction.
         *
         * use a similar concept than layout for the PrintTransaction;
         * must be possible to influence display by setting classes or directly printing
         *
         * how to solve problems with printables which are dependent on printables before?
         *
         * also refactor to use interface instead of callable for better maintainability
         */
        $emptyBuffer = $this->printBuffer->invokePrintablesCallable();

        if ($this->pdfDocument->causesPageBreak($emptyBuffer)) {
            $this->pdfDocument->startNewPage();
        }

        $startX = $this->getColumnCursors()[0]->getXCoordinate();

        $before = $this->pdfDocument->getCursor();
        $before->setX($startX);
        $after = $this->pdfDocument->cursorAfterwardsIfPrinted($emptyBuffer);

        $after->setX($startX + $this->fullWidth);

        $callable($this->pdfDocument, $this->fullWidth, $after);
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
        $width = $this->getColumnWidths()[$this->getActiveColumn()];
        $this->printBuffer->bufferPrintable($callable, [$width]);
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
}
