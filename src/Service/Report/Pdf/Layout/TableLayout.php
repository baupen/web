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
use App\Service\Report\Document\Interfaces\Layout\Base\PrintTransactionInterface;
use App\Service\Report\Document\Interfaces\Layout\TableLayoutInterface;
use App\Service\Report\Document\Interfaces\Layout\TableRowLayoutInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocument\PdfDocumentTransactionInterface;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Layout\Supporting\PrintTransaction;

class TableLayout implements TableLayoutInterface
{
    /**
     * @var PdfDocumentTransactionInterface
     */
    private $pdfDocument;

    /**
     * @var float
     */
    private $width;

    /**
     * @var float
     */
    private $columnGutter;

    /**
     * @var float[]
     */
    private $columnWidths;

    /**
     * @var int
     */
    private $columnCount;

    /**
     * @var TableRowLayout[]
     */
    private $rows;

    /**
     * @var callable
     */
    private $onRowCommit;

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

        $this->columnWidths = $this->calculateColumnWidths($columnConfiguration);
        $this->columnCount = \count($columnConfiguration);
    }

    /**
     * @param callable $callable
     */
    public function setOnRowCommit(callable $callable): void
    {
        $this->onRowCommit = $callable;
    }

    /**
     * @return TableRowLayoutInterface
     */
    public function startNewRow()
    {
        $layout = new TableRowLayout($this->pdfDocument, $this->columnGutter, $this->width, $this->columnWidths);
        $this->rows[] = $layout;

        return $layout;
    }

    /**
     * will produce a transaction with the to-be-printed document.
     *
     * @return PrintTransactionInterface
     */
    public function getTransaction()
    {
        $flushRows = function () {
            foreach ($this->rows as $row) {
                $transaction = $row->getTransaction();
                if ($this->onRowCommit !== null) {
                    $this->onRowCommit($transaction);
                }
                $transaction->commit();
            }
        };

        return new PrintTransaction($this->pdfDocument, $this->width, $flushRows);
    }

    /**
     * @param ColumnConfiguration[] $columnConfiguration
     *
     * @throws \Exception
     *
     * @return float[]
     */
    private function calculateColumnWidths(array $columnConfiguration)
    {
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

        return $widths;
    }
}
