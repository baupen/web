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

use App\Service\Report\Document\Interfaces\Layout\ColumnLayoutInterface;
use App\Service\Report\Pdf\Cursor;
use App\Service\Report\Pdf\Interfaces\CustomPrinterLayoutInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class ColumnLayout implements ColumnLayoutInterface, CustomPrinterLayoutInterface
{
    /**
     * @var PdfDocumentInterface
     */
    private $pdfDocument;

    /**
     * @var int
     */
    private $columnCount;

    /**
     * @var float
     */
    private $startCursor;

    /**
     * @var float
     */
    private $totalWidth;

    /**
     * @var float
     */
    private $columnWidth;

    /**
     * @var float
     */
    private $columnGutter;

    /**
     * @var int
     */
    private $activeColumn = 0;

    /**
     * @var Cursor[]
     */
    private $columnCursors;

    /**
     * @var bool
     */
    private $isAutoColumn;

    /**
     * ColumnLayout constructor.
     *
     * @param PdfDocumentInterface $pdfDocument
     * @param int $columnCount
     * @param float $columnGutter
     * @param float $width
     */
    public function __construct(PdfDocumentInterface $pdfDocument, int $columnCount, float $columnGutter, float $width)
    {
        $this->pdfDocument = $pdfDocument;
        $this->columnCount = $columnCount;
        $this->columnGutter = $columnGutter;
        $this->totalWidth = $width;

        $gutterSpace = ($columnCount - 1) * $columnGutter;
        $columnWidth = (float)($width - $gutterSpace) / $columnCount;
        $this->columnWidth = $columnWidth;

        $this->startCursor = $pdfDocument->getCursor();
        $this->columnCursors[$this->activeColumn] = $this->startCursor;
    }

    /**
     * ensures the next printed elements are printed in the specified column
     * will throw an exception if the column region does not exist.
     *
     * @param int $column
     *
     * @throws \Exception
     */
    public function goToColumn(int $column)
    {
        if ($column >= $this->columnCount) {
            throw new \Exception('column must be smaller than the column count');
        }

        $this->columnCursors[$this->activeColumn] = $this->pdfDocument->getCursor();

        // set correct cursor/page
        $this->pdfDocument->setCursor($this->startCursor->setX($this->getColumnStart($column)));

        // save
        $this->activeColumn = $column;
    }

    /**
     * @param $currentColumn
     *
     * @return float|float
     */
    public function getColumnStart($currentColumn)
    {
        $width = $this->columnWidth + $this->columnGutter;

        return $width * $currentColumn + $this->startCursor->getXCoordinate();
    }

    /**
     * will end the columned layout.
     */
    public function endLayout()
    {
        $lowestCursor = $this->startCursor;
        foreach ($this->columnCursors as $columnCursor) {
            $lowestCursor = $lowestCursor->isLowerThan($columnCursor) ? $lowestCursor : $columnCursor;
        }

        $this->pdfDocument->setCursor($lowestCursor->setX($this->startCursor->getXCoordinate()));
    }

    /**
     * when printing something, the column with the least content is chosen automatically.
     *
     * @param bool $active
     */
    public function setAutoColumn(bool $active)
    {
        $this->isAutoColumn = $active;
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
        // set active cursor to highest cursor
        if ($this->isAutoColumn) {
            $highestCursor = $this->columnCursors[$this->activeColumn];

            foreach ($this->columnCursors as $columnCursor) {
                $highestCursor = $columnCursor->isLowerThan($highestCursor) ? $highestCursor : $columnCursor;
            }

            $this->pdfDocument->setCursor($highestCursor);
        }

        $callable($this->pdfDocument, $this->columnWidth);
    }
}
