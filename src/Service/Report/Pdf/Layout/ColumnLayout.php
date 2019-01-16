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
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Layout\Base\ColumnedLayout;

class ColumnLayout extends ColumnedLayout implements ColumnLayoutInterface
{
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
     * @param float $totalWidth
     */
    public function __construct(PdfDocumentInterface $pdfDocument, int $columnCount, float $columnGutter, float $totalWidth)
    {
        $gutterSpace = ($columnCount - 1) * $columnGutter;
        $columnWidth = (float)($totalWidth - $gutterSpace) / $columnCount;
        $columnWidths = [];
        for ($i = 0; $i < $columnCount; ++$i) {
            $columnWidths[] = $columnWidth;
        }

        parent::__construct($pdfDocument, $columnGutter, $totalWidth, $columnWidths);
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
     *
     * @throws \Exception
     */
    public function registerPrintable(callable $callable)
    {
        // set active cursor to highest cursor
        if ($this->isAutoColumn) {
            // prepare variables
            $columnCursors = $this->getColumnCursors();
            $highestColumn = 0;
            $highestCursor = $columnCursors[0];
            $columnCount = $this->getColumnCount();

            // get highest cursor
            for ($i = 1; $i < $columnCount; ++$i) {
                $otherCursor = $columnCursors[$i];

                if (!$highestCursor->isLowerOnPageThan($otherCursor)) {
                    $highestColumn = $i;
                    $highestCursor = $otherCursor;
                }
            }

            $this->goToColumn($highestColumn);
        }

        parent::registerPrintable($callable);
    }
}
