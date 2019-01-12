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

use App\Service\Report\Document\Interfaces\Configuration\ColumnConfiguration;
use App\Service\Report\Document\Interfaces\Layout\ColumnLayoutInterface;
use App\Service\Report\Document\Interfaces\Layout\TableLayoutInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class TableLayout implements TableLayoutInterface
{
    /**
     * @var PdfDocumentInterface
     */
    private $pdfDocument;

    /**
     * @var float
     */
    private $columnGutter;

    /**
     * @var float[]
     */
    private $columnWidths;

    /**
     * @var float
     */
    private $width;

    /**
     * @var int
     */
    private $columnCount;

    /**
     * @param PdfDocumentInterface $pdfDocument
     * @param float $width
     * @param float $columnGutter
     * @param ColumnConfiguration[] $columnConfiguration
     *
     * @throws \Exception
     */
    public function __construct(PdfDocumentInterface $pdfDocument, float $width, float $columnGutter, array $columnConfiguration)
    {
        $this->pdfDocument = $pdfDocument;
        $this->width = $width;
        $this->columnGutter = $columnGutter;

        $this->columnCount = \count($columnConfiguration);
        $this->setColumnWidths($columnConfiguration);
    }

    /**
     * will end the columned layout.
     */
    public function endLayout()
    {
        // no specials here
    }

    /**
     * @param ColumnConfiguration[] $columnConfiguration
     *
     * @throws \Exception
     */
    private function setColumnWidths(array $columnConfiguration)
    {
        if ($this->columnWidths !== null) {
            return;
        }

        $gutterSpace = (\count($columnConfiguration) - 1) * $this->columnGutter;
        $availableWidth = $this->width - $gutterSpace;

        $expandColumns = [];
        $widths = [];
        for ($i = 0; $i < $this->columnCount; ++$i) {
            $column = $columnConfiguration[$i];
            if ($column->getSizing() === ColumnConfiguration::SIZING_EXPAND) {
                $expandColumns[] = $i;
            } elseif ($column->getSizing() === ColumnConfiguration::SIZING_BY_TEXT) {
                $text = $column->getText();
                $width = $this->pdfDocument->calculateWidthOfText($text);

                $availableWidth -= $width;
                $widths[$i] = $width;
            } else {
                throw new \Exception('sizing mode ' . $column->getSizing() . ' not supported');
            }
        }

        // calculate expand widths
        $expandColumnsCount = \count($expandColumns);
        if ($expandColumnsCount > 0) {
            $expandColumnWidth = $availableWidth / $expandColumnsCount;
            foreach ($expandColumns as $expandColumn) {
                $widths[$expandColumn] = $expandColumnWidth;
            }
        }
    }

    /**
     * @param string[] $content
     */
    private function printRowInternal(array $content)
    {
        $cursor = $this->pdfDocument->getCursor();
        $lowestCursor = $cursor;

        $startX = $cursor->getXCoordinate();
        $gutterWidth = $this->columnGutter;
        for ($i = 0; $i < $this->columnCount; ++$i) {
            $columnWidth = $this->columnWidths[$i];
            $this->pdfDocument->printText($content[$i], $columnWidth);

            $currentCursor = $this->pdfDocument->getCursor();
            $lowestCursor = $currentCursor->isLowerThan($lowestCursor) ? $lowestCursor : $currentCursor;

            $startX += $columnWidth + $gutterWidth;
            $this->pdfDocument->setCursor($cursor->setX($startX));
        }

        $this->pdfDocument->setCursor($lowestCursor->setX($cursor->getXCoordinate()));
    }

    /**
     * @return ColumnLayoutInterface
     */
    public function startNewRow()
    {
        return ColumnLayout::createWithPredefinedWidths($this->pdfDocument, $this->columnCount, $this->columnGutter, $this->columnWidths);
    }
}
