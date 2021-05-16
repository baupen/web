<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Email;

trait IssueCountTrait
{
    private int $openCount = 0;

    private int $resolvedCount = 0;

    private int $closedCount = 0;

    /**
     * @param IssueCountTrait $other
     */
    protected function addIssueCount($other)
    {
        $this->openCount += $other->getOpenCount();
        $this->resolvedCount += $other->getResolvedCount();
        $this->closedCount += $other->getClosedCount();
    }

    public function getOpenCount(): int
    {
        return $this->openCount;
    }

    public function setOpenCount(int $openCount): void
    {
        $this->openCount = $openCount;
    }

    public function getResolvedCount(): int
    {
        return $this->resolvedCount;
    }

    public function setResolvedCount(int $resolvedCount): void
    {
        $this->resolvedCount = $resolvedCount;
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
