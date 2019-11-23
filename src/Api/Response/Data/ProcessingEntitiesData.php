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

class ProcessingEntitiesData
{
    /**
     * @var string[]
     */
    private $successfulIds = [];

    /**
     * @var string[]
     */
    private $failedIds = [];

    /**
     * @var string[]
     */
    private $skippedIds = [];

    public function addSuccessfulId(string $id)
    {
        $this->successfulIds[] = $id;
    }

    /**
     * @return string[]
     */
    public function getSuccessfulIds(): array
    {
        return $this->successfulIds;
    }

    public function addFailedId(string $id)
    {
        $this->failedIds[] = $id;
    }

    /**
     * @return string[]
     */
    public function getFailedIds(): array
    {
        return $this->failedIds;
    }

    public function addSkippedId(string $id)
    {
        $this->skippedIds[] = $id;
    }

    /**
     * @return string[]
     */
    public function getSkippedIds(): array
    {
        return $this->skippedIds;
    }
}
