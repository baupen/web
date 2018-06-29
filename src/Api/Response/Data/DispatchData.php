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

class DispatchData
{
    /**
     * @var int
     */
    private $sentEmailCount;

    /**
     * @var int
     */
    private $errorEmailCount;

    /**
     * @var int
     */
    private $skippedEmailCount;

    /**
     * @return int
     */
    public function getSentEmailCount(): int
    {
        return $this->sentEmailCount;
    }

    /**
     * @param int $sentEmailCount
     */
    public function setSentEmailCount(int $sentEmailCount): void
    {
        $this->sentEmailCount = $sentEmailCount;
    }

    /**
     * @return int
     */
    public function getErrorEmailCount(): int
    {
        return $this->errorEmailCount;
    }

    /**
     * @param int $errorEmailCount
     */
    public function setErrorEmailCount(int $errorEmailCount): void
    {
        $this->errorEmailCount = $errorEmailCount;
    }

    /**
     * @return int
     */
    public function getSkippedEmailCount(): int
    {
        return $this->skippedEmailCount;
    }

    /**
     * @param int $skippedEmailCount
     */
    public function setSkippedEmailCount(int $skippedEmailCount): void
    {
        $this->skippedEmailCount = $skippedEmailCount;
    }
}
