<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Issue;

class Summary
{
    /**
     * @var int
     */
    private $newCount = 0;

    /**
     * @var int
     */
    private $openCount = 0;

    /**
     * @var int
     */
    private $inspectableCount = 0;

    /**
     * @var int
     */
    private $closedCount = 0;

    public function writeFrom(self $summary)
    {
        $this->newCount = $summary->getNewCount();
        $this->openCount = $summary->getOpenCount();
        $this->inspectableCount = $summary->getInspectableCount();
        $this->closedCount = $summary->getClosedCount();
    }

    public function getNewCount(): int
    {
        return $this->newCount;
    }

    public function setNewCount(int $newCount): void
    {
        $this->newCount = $newCount;
    }

    public function getOpenCount(): int
    {
        return $this->openCount;
    }

    public function setOpenCount(int $openCount): void
    {
        $this->openCount = $openCount;
    }

    public function getInspectableCount(): int
    {
        return $this->inspectableCount;
    }

    public function setInspectableCount(int $inspectableCount): void
    {
        $this->inspectableCount = $inspectableCount;
    }

    public function getClosedCount(): int
    {
        return $this->closedCount;
    }

    public function setClosedCount(int $closedCount): void
    {
        $this->closedCount = $closedCount;
    }
}
