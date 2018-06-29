<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data;

class FoyerData
{
    /**
     * @var int
     */
    private $processedCount;

    /**
     * @return int
     */
    public function getProcessedCount(): int
    {
        return $this->processedCount;
    }

    /**
     * @param int $processedCount
     */
    public function setProcessedCount(int $processedCount): void
    {
        $this->processedCount = $processedCount;
    }
}
