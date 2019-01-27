<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Service\Report\Document\Pdf\Transaction;

use PHPUnit\Framework\TestCase;

class PrintTransactionTest extends TestCase
{
    public function testIsLowerOnPage_onlyPageSet_correctResult()
    {
        $highCursor = new Cursor(0, 0, 0);
        $lowCursor = new Cursor(0, 0, 1);

        $this->ensureHighLow($highCursor, $lowCursor);
    }
}
