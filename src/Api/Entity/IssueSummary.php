<?php

namespace App\Api\Entity;

use App\Service\Analysis\CraftsmanIssueAnalysis;
use App\Service\Analysis\IssueAnalysis;
use Symfony\Component\Serializer\Annotation\Groups;

class IssueSummary
{
    #[Groups(['issue:read', 'craftsman:read'])]
    private int $newCount;

    #[Groups(['issue:read', 'craftsman:read'])]
    private int $openCount;

    #[Groups(['issue:read', 'craftsman:read'])]
    private int $inspectableCount;

    #[Groups(['issue:read', 'craftsman:read'])]
    private int $closedCount;

    public static function createFromCraftsmanIssueAnalysis(CraftsmanIssueAnalysis $craftsmanIssueAnalysis): self
    {
        $self = new self();

        $self->newCount = 0;
        $self->openCount = $craftsmanIssueAnalysis->getOpenCount();
        $self->inspectableCount = $craftsmanIssueAnalysis->getInspectableCount();
        $self->closedCount = $craftsmanIssueAnalysis->getClosedCount();

        return $self;
    }

    public static function createFromIssueAnalysis(IssueAnalysis $issueAnalysis): self
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
