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

class ProcessingData
{
    /**
     * @var int
     */
    private $successful = 0;

    /**
     * @var int
     */
    private $failed = 0;

    /**
     * @var int
     */
    private $skipped = 0;

    /**
     * @return int
     */
    public function getSuccessful(): int
    {
        return $this->successful;
    }

    /**
     * @param int $successful
     */
    public function setSuccessful(int $successful): void
    {
        $this->successful = $successful;
    }

    /**
     * @return int
     */
    public function getFailed(): int
    {
        return $this->failed;
    }

    /**
     * @param int $failed
     */
    public function setFailed(int $failed): void
    {
        $this->failed = $failed;
    }

    /**
     * @return int
     */
    public function getSkipped(): int
    {
        return $this->skipped;
    }

    /**
     * @param int $skipped
     */
    public function setSkipped(int $skipped): void
    {
        $this->skipped = $skipped;
    }
}
