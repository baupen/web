<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Interfaces\PdfDocument;

use App\Service\Report\Pdf\Cursor;

interface PdfDocumentTransactionInterface extends PdfDocumentStateInterface
{
    /**
     * @param \Closure $printClosure
     *
     * @return bool
     */
    public function causesPageBreak(\Closure $printClosure);

    /**
     * @param \Closure $printClosure
     *
     * @return Cursor
     */
    public function cursorAfterwardsIfPrinted(\Closure $printClosure);

    /**
     * @param string $text
     *
     * @return float
     */
    public function calculateWidthOfText(string $text);
}
