<?php

namespace App\Service\Report\Email;

trait IssueCountDeltaTrait
{
    private int $openCountDelta = 0;

    private int $resolvedCountDelta = 0;

    private int $closedCountDelta = 0;

    protected function addIssueCountDelta(CraftsmanDeltaReport|ConstructionSiteReport $other): void
    {
        $this->openCountDelta += $other->getOpenCountDelta();
        $this->resolvedCountDelta += $other->getResolvedCountDelta();
        $this->closedCountDelta += $other->getClosedCountDelta();
    }

    public function getOpenCountDelta(): int
    {
        return $this->openCountDelta;
    }

    public function setOpenCountDelta(int $openCountDelta): void
    {
        $this->openCountDelta = $openCountDelta;
    }

    public function getResolvedCountDelta(): int
    {
        return $this->resolvedCountDelta;
    }

    public function setResolvedCountDelta(int $resolvedCountDelta): void
    {
        $this->resolvedCountDelta = $resolvedCountDelta;
    }

    public function getClosedCountDelta(): int
    {
        return $this->closedCountDelta;
    }

    public function setClosedCountDelta(int $closedCountDelta): void
    {
        $this->closedCountDelta = $closedCountDelta;
    }
}
