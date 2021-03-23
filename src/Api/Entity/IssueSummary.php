<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity;

use App\Service\Analysis\CraftsmanIssueAnalysis;
use App\Service\Analysis\IssueAnalysis;

class IssueSummary
{
    private int $newCount;

    private int $openCount;

    private int $inspectableCount;

    private int $closedCount;

    public static function createFromCraftsmanIssueAnalysis(CraftsmanIssueAnalysis $craftsmanIssueAnalysis)
    {
        $self = new self();

        $self->newCount = 0;
        $self->openCount = $craftsmanIssueAnalysis->getOpenCount();
        $self->inspectableCount = $craftsmanIssueAnalysis->getInspectableCount();
        $self->closedCount = $craftsmanIssueAnalysis->getClosedCount();

        return $self;
    }

    public static function createFromIssueAnalysis(IssueAnalysis $issueAnalysis)
    {
        $self = new self();
        $self->writeFromIssueAnalysis($issueAnalysis);

        return $self;
    }

    protected function writeFromIssueAnalysis(IssueAnalysis $issueAnalysis)
    {
        $this->newCount = $issueAnalysis->getNewCount();
        $this->openCount = $issueAnalysis->getOpenCount();
        $this->inspectableCount = $issueAnalysis->getInspectableCount();
        $this->closedCount = $issueAnalysis->getClosedCount();
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
