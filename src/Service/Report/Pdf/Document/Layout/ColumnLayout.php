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

use App\Service\Report\Document\Layout\ColumnLayoutInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Printer;

class ColumnLayout implements ColumnLayoutInterface
{
    /**
     * @var Printer
     */
    private $printer;

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
     * @param Printer $printer
     * @param PdfDocumentInterface $pdfDocument
     * @param int $columnCount
     * @param float $columnGutter
     * @param float $width
     */
    public function __construct(Printer $printer, PdfDocumentInterface $pdfDocument, int $columnCount, float $columnGutter, float $width)
    {
        $this->printer = $printer;
        $this->pdfDocument = $pdfDocument;
        $this->columnCount = $columnCount;
        $this->columnGutter = $columnGutter;
        $this->totalWidth = $width;

        $gutterSpace = ($this->columnCount - 1) * $this->columnGutter;
        $this->columnWidth = (float)($this->totalWidth - $gutterSpace) / $this->columnCount;

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
     * @param string $title
     */
    public function printTitle(string $title)
    {
        $this->printer->printTitle($title, $this->columnWidth);
    }

    /**
     * @param string $paragraph
     */
    public function printParagraph(string $paragraph)
    {
        $this->printer->printParagraph($paragraph, $this->columnWidth);
    }

    /**
     * @param string[] $keyValues
     */
    public function printKeyValueParagraph(array $keyValues)
    {
        $this->printer->printKeyValueParagraph($keyValues, $this->columnWidth);
    }

    /**
     * @param string $header
     */
    public function printRegionHeader(string $header)
    {
        $this->printer->printRegionHeader($header, $this->columnWidth);
    }

    /**
     * @param string[] $header
     * @param string[][] $content
     */
    public function printTable(array $header, array $content)
    {
        $this->printer->printTable($header, $content, $this->columnWidth);
    }

    /**
     * @param string $filePath
     */
    public function printImage(string $filePath)
    {
        $this->printer->printImage($filePath, $this->columnWidth);
    }
}
