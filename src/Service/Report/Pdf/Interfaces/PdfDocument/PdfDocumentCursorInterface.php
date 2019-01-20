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

interface PdfDocumentCursorInterface
{
    /**
     * returns the active cursor position as an array of [$xCoordinate, $yCoordinate, $page].
     *
     * @return Cursor
     */
    public function getCursor();

    /**
     * @param Cursor $cursor
     */
    public function setCursor(Cursor $cursor);

    /**
     * starts a new page & sets the cursor to the next page.
     */
    public function startNewPage();
}
