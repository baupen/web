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

use App\Service\Report\Document\Interfaces\Layout\Base\ColumnedLayoutInterface;
use App\Service\Report\Pdf\Cursor;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class ColumnedLayout implements ColumnedLayoutInterface
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
     * @var float[]
     */
    private $columnStarts;

    /**
     * @var float[]
     */
    private $columnWidths;

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
     * ColumnLayout constructor.
     *
     * @param PdfDocumentInterface $pdfDocument
     * @param float $columnGutter
     * @param float $totalWidth
     * @param float[] $widths
     */
    protected function __construct(PdfDocumentInterface $pdfDocument, float $columnGutter, float $totalWidth, array $widths)
    {
        $this->pdfDocument = $pdfDocument;
        $this->columnCount = \count($widths);
        $this->columnGutter = $columnGutter;
        $this->totalWidth = $totalWidth;
        $this->columnWidths = $widths;

        $this->activeColumn = 0;
        $this->startCursor = $pdfDocument->getCursor();
        $this->columnCursors[$this->activeColumn] = $this->startCursor;

        $nextWidth = 0;
        $currentColumn = 0;
        do {
            $this->columnStarts[$currentColumn] = $nextWidth;
            $nextWidth += $this->columnWidths[$currentColumn] + $this->columnGutter;
        } while (++$currentColumn < $this->columnCount);
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
        $xStart = $this->columnStarts[$column] + $this->startCursor->getXCoordinate();
        $this->pdfDocument->setCursor($this->startCursor->setX($xStart));

        // save
        $this->activeColumn = $column;
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
     * @return float[]
     */
    protected function getColumnWidths(): array
    {
        return $this->columnWidths;
    }

    /**
     * @return Cursor[]
     */
    protected function getColumnCursors(): array
    {
        return $this->columnCursors;
    }

    /**
     * @return int
     */
    protected function getActiveColumn(): int
    {
        return $this->activeColumn;
    }
}
