<?php

namespace App\Api\Dto;

use App\Api\Dto\CraftsmanStatistics\IssueSummaryDto;
use App\Entity\Craftsman;
use App\Service\Analysis\CraftsmanAnalysis;
use Symfony\Component\Serializer\Attribute\Groups;

class CraftsmanStatisticsDto
{
    #[Groups(['craftsman-statistics:read'])]
    private Craftsman $craftsman;

    #[Groups(['craftsman-statistics:read'])]
    private ?IssueSummaryDto $issueSummary = null;

    #[Groups(['craftsman-statistics:read'])]
    public int $issueUnreadCount;

    #[Groups(['craftsman-statistics:read'])]
    public int $issueOverdueCount;

    #[Groups(['craftsman-statistics:read'])]
    public ?\DateTimeImmutable $nextDeadline;

    #[Groups(['craftsman-statistics:read'])]
    public ?\DateTimeImmutable $lastEmailReceived;

    #[Groups(['craftsman-statistics:read'])]
    public ?\DateTimeImmutable $lastVisitOnline;

    #[Groups(['craftsman-statistics:read'])]
    public ?\DateTimeImmutable $lastIssueResolved;

    public static function create(CraftsmanAnalysis $craftsmanAnalysis): self
    {
        $self = new self();

        $self->craftsman = $craftsmanAnalysis->getCraftsman();
        $self->issueSummary = IssueSummaryDto::create($craftsmanAnalysis->getIssueAnalysis());
        $self->issueUnreadCount = $craftsmanAnalysis->getIssueAnalysis()->getUnreadCount();
        $self->issueOverdueCount = $craftsmanAnalysis->getIssueAnalysis()->getOverdueCount();
        $self->nextDeadline = $craftsmanAnalysis->getNextDeadline();
        $self->lastEmailReceived = $craftsmanAnalysis->getLastEmailReceived();
        $self->lastVisitOnline = $craftsmanAnalysis->getLastVisitOnline();
        $self->lastIssueResolved = $craftsmanAnalysis->getLastIssueResolved();

        return $self;
    }

    public function getCraftsman(): Craftsman
    {
        return $this->craftsman;
    }

    public function getIssueSummary(): ?IssueSummaryDto
    {
        return $this->issueSummary;
    }

    public function getIssueUnreadCount(): int
    {
        return $this->issueUnreadCount;
    }

    public function getIssueOverdueCount(): int
    {
        return $this->issueOverdueCount;
    }

    public function getNextDeadline(): ?\DateTimeImmutable
    {
        return $this->nextDeadline;
    }

    public function getLastEmailReceived(): ?\DateTimeImmutable
    {
        return $this->lastEmailReceived;
    }

    public function getLastVisitOnline(): ?\DateTimeImmutable
    {
        return $this->lastVisitOnline;
    }

    public function getLastIssueResolved(): ?\DateTimeImmutable
    {
        return $this->lastIssueResolved;
    }
}
