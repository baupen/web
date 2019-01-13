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
    private $totalWidth;

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

        $cursor = $pdfDocument->getCursor();
        $nextXStart = $cursor->getXCoordinate();
        $currentColumn = 0;
        do {
            $this->columnCursors[$currentColumn] = $cursor->setX($nextXStart);
            $nextXStart += $this->columnWidths[$currentColumn] + $this->columnGutter;
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

        // save current cursor
        $this->columnCursors[$this->activeColumn] = $this->pdfDocument->getCursor();

        // set new cursor
        $this->activeColumn = $column;
        $this->pdfDocument->setCursor($this->columnCursors[$this->activeColumn]);
    }

    /**
     * will end the columned layout.
     */
    public function endLayout()
    {
        $lowestCursor = $this->columnCursors[0];
        for ($i = 1; $i < $this->columnCount; ++$i) {
            $other = $this->columnCursors[$i];
            if ($other->isLowerOnPageThan($lowestCursor)) {
                $lowestCursor = $other;
            }
        }

        $this->pdfDocument->setCursor($lowestCursor->setX($this->columnCursors[0]->getXCoordinate()));
    }

    /**
     * @return int
     */
    protected function getColumnCount(): int
    {
        return $this->columnCount;
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

    /**
     * register a callable which prints to the pdf document
     * The position of the cursor at the time the callable is invoked is decided by the layout
     * ensure the cursor is below the printed content after the callable is finished to not mess up the layout.
     *
     * @param callable $callable takes a PdfDocumentInterface as first argument and the width as second
     */
    protected function registerPrintable(callable $callable)
    {
        $callable($this->pdfDocument, $this->columnWidths[$this->activeColumn]);
    }
}
