<?php

namespace App\Service\Report\Email;

trait IssueCountTrait
{
    private int $openCount = 0;

    private int $resolvedCount = 0;

    private int $closedCount = 0;
    protected function addIssueCount(ConstructionSiteReport|CraftsmanDeltaReport $other): void
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
