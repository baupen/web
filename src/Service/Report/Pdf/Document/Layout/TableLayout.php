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

use App\Service\Report\Document\Interfaces\Configuration\Table;
use App\Service\Report\Document\Interfaces\Configuration\TableColumn;
use App\Service\Report\Document\Interfaces\Layout\TableLayoutInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class TableLayout implements TableLayoutInterface
{
    /**
     * @var PdfDocumentInterface
     */
    private $pdfDocument;

    /**
     * @var Table
     */
    private $tableConfiguration;

    /**
     * @var TableColumn[]
     */
    private $tableColumnConfiguration;

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
     * @param Table $tableConfiguration
     * @param array $tableColumnConfiguration
     */
    public function __construct(PdfDocumentInterface $pdfDocument, float $width, float $columnGutter, Table $tableConfiguration, array $tableColumnConfiguration)
    {
        $this->pdfDocument = $pdfDocument;
        $this->width = $width;
        $this->columnGutter = $columnGutter;
        $this->tableConfiguration = $tableConfiguration;
        $this->tableColumnConfiguration = $tableColumnConfiguration;

        $this->columnCount = \count($tableColumnConfiguration);
    }

    /**
     * will end the columned layout.
     */
    public function endLayout()
    {
        // TODO: Implement endLayout() method.
    }

    /**
     * @param string[] $header
     *
     * @throws \Exception
     */
    public function printHeader(array $header)
    {
        $this->ensureColumnWidthSet($header);
        $this->printRowInternal($header);
    }

    /**
     * @param string[] $row
     *
     * @throws \Exception
     */
    public function printRow(array $row)
    {
        $this->ensureColumnWidthSet(null);
        $this->printRowInternal($row);
    }

    /**
     * @param string[][] $rows
     *
     * @throws \Exception
     */
    public function printRows(array $rows)
    {
        if (\count($rows) === 0) {
            return;
        }

        $this->ensureColumnWidthSet($rows[0]);

        foreach ($rows as $row) {
            $this->printRowInternal($row);
        }
    }

    /**
     * @param array|null $header
     *
     * @throws \Exception
     */
    private function ensureColumnWidthSet(array $header = null)
    {
        if ($this->columnWidths !== null) {
            return;
        }

        $gutterSpace = (\count($this->tableColumnConfiguration) - 1) * $this->columnGutter;
        $availableWidth = $this->width - $gutterSpace;

        $expandColumns = [];
        $widths = [];
        for ($i = 0; $i < $this->columnCount; ++$i) {
            $column = $this->tableColumnConfiguration[$i];
            if ($column->getSizing() === TableColumn::SIZING_EXPAND) {
                $expandColumns[] = $i;
            } elseif ($column->getSizing() === TableColumn::SIZING_BY_HEADER) {
                if ($header === null) {
                    throw new \Exception('sizing mode ' . TableColumn::SIZING_BY_HEADER . ' not supported if no header is printed');
                }

                $width = $this->calculateWidthOfText($header[$i]);
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
            foreach ($expandColumns as $exoandColumn) {
                $widths[$exoandColumn] = $expandColumnWidth;
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
}
