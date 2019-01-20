<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Document\Interfaces\Layout\Base;

use App\Service\Report\Pdf\Cursor;

interface PrintTransactionInterface
{
    /**
     * get the area of the to-be printed area by this transaction
     * returns an array where the first entry is the start cursor; the second the end cursor.
     *
     * @return Cursor[]
     */
    public function calculatePrintArea();

    /**
     * prints the contained components.
     */
    public function commit();
}
