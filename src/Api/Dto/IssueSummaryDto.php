<?php

namespace App\Api\Dto;

use App\Service\Analysis\IssueAnalysis;
use Symfony\Component\Serializer\Annotation\Groups;

class IssueSummaryDto
{
    #[Groups(['issue-summary:read'])]
    private int $newCount;

    #[Groups(['issue-summary:read'])]
    private int $openCount;

    #[Groups(['issue-summary:read'])]
    private int $inspectableCount;

    #[Groups(['issue-summary:read'])]
    private int $closedCount;

    public static function create(IssueAnalysis $issueAnalysis): self
    {
        $self = new self();
        $self->writeFromIssueAnalysis($issueAnalysis);

        return $self;
    }

    protected function writeFromIssueAnalysis(IssueAnalysis $issueAnalysis): void
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

    public function getOpenCount(): int
    {
        return $this->openCount;
    }

    public function getInspectableCount(): int
    {
        return $this->inspectableCount;
    }

    public function getClosedCount(): int
    {
        return $this->closedCount;
    }
}
