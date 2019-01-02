<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Document\Layout;

use App\Service\Report\Document\Interfaces\Layout\ColumnLayoutInterface;
use App\Service\Report\Pdf\Document\Layout\Base\BaseLayout;
use App\Service\Report\Pdf\Document\PdfPrinter;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class ColumnLayout extends BaseLayout implements ColumnLayoutInterface
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
    private $startY;

    /**
     * @var float
     */
    private $startX;

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
    private $startPage;

    /**
     * @var int
     */
    private $activeColumn = 0;

    /**
     * @var int
     */
    private $maxPage;

    /**
     * @var float
     */
    private $maxY;

    /**
     * ColumnLayout constructor.
     *
     * @param PdfPrinter $printer
     * @param PdfDocumentInterface $pdfDocument
     * @param int $columnCount
     * @param float $columnGutter
     * @param float $width
     */
    public function __construct(PdfPrinter $printer, PdfDocumentInterface $pdfDocument, int $columnCount, float $columnGutter, float $width)
    {
        $gutterSpace = ($columnCount - 1) * $columnGutter;
        $columnWidth = (float)($width - $gutterSpace) / $columnCount;

        parent::__construct($printer, $columnWidth);

        $this->pdfDocument = $pdfDocument;
        $this->columnCount = $columnCount;
        $this->columnGutter = $columnGutter;
        $this->totalWidth = $columnWidth;
        $this->columnWidth = $columnWidth;

        $cursor = $pdfDocument->getCursor();
        $this->startPage = $pdfDocument->getPage();
        $this->startX = $cursor[0];
        $this->startY = $cursor[1];

        $this->maxPage = $this->startPage;
        $this->maxY = $this->startY;
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

        $this->preserveCursorMax();

        // set correct cursor/page
        $this->pdfDocument->setPage($this->startPage);
        $this->pdfDocument->setCursor($this->getColumnStart($column), $this->startY);

        // save
        $this->activeColumn = $column;
    }

    /**
     * preserves the max page / y reached by the cursor.
     */
    private function preserveCursorMax()
    {
        $yCoordinate = $this->pdfDocument->getCursor()[1];
        $this->maxY = max($this->maxY, $yCoordinate);
        $this->maxPage = max($this->maxPage, $this->pdfDocument->getPage());
    }

    /**
     * @param $currentColumn
     *
     * @return float|float
     */
    public function getColumnStart($currentColumn)
    {
        $width = $this->columnWidth + $this->columnGutter;

        return $width * $currentColumn + $this->startX;
    }

    /**
     * will end the columned layout.
     */
    public function endLayout()
    {
        $this->preserveCursorMax();

        $this->pdfDocument->setCursor($this->startX, $this->maxY);
    }

    /**
     * when printing something, the column with the least content is chosen automatically.
     */
    public function setAutoColumn()
    {
        // TODO: Implement setAutoColumn() method.
    }
}
