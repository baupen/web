<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Analysis;

trait IssueCountAnalysisTrait
{
    private int $openCount = 0;

    private int $inspectableCount = 0;

    private int $closedCount = 0;

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
