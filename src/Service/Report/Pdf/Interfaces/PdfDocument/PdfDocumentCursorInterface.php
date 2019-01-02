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

interface PdfDocumentCursorInterface
{
    /**
     * returns the active cursor position as an array of [$xCoordinate, $yCoordinate].
     *
     * @return int[]
     */
    public function getCursor();

    /**
     * @param float $xCoordinate
     * @param float $yCoordinate
     */
    public function setCursor(float $xCoordinate, float $yCoordinate);

    /**
     * @param \Closure $printClosure
     *
     * @return bool
     */
    public function causesPageBreak(\Closure $printClosure);

    /**
     * @return int
     */
    public function getPage();

    /**
     * @param int $page
     */
    public function setPage(int $page);

    /**
     * starts a new page & sets the cursor to the next page.
     */
    public function startNewPage();
}
