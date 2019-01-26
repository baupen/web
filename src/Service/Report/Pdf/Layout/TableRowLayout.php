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
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Layout\Base\StatefulColumnedLayout;

class TableRowLayout extends StatefulColumnedLayout implements TableRowLayoutInterface
{
    /**
     * ColumnLayout constructor.
     *
     * @param PdfDocumentInterface $pdfDocument
     * @param float $columnGutter
     * @param float $totalWidth
     * @param array $columnWidths
     */
    public function __construct(PdfDocumentInterface $pdfDocument, float $columnGutter, float $totalWidth, array $columnWidths)
    {
        parent::__construct($pdfDocument, $columnGutter, $totalWidth, $columnWidths);
    }
}
