<?php

namespace App\Service\Analysis;

use App\Entity\Craftsman;

class CraftsmanAnalysis
{
    private Craftsman $craftsman;

    private CraftsmanIssueAnalysis $issueAnalysis;

    private ?\DateTimeImmutable $nextDeadline = null;

    private ?\DateTimeImmutable $lastEmailReceived = null;

    private ?\DateTimeImmutable $lastVisitOnline = null;

    private ?\DateTimeImmutable $lastIssueResolved = null;

    public static function create(Craftsman $craftsman, CraftsmanIssueAnalysis $issueAnalysis): self
    {
        $self = new self();

        $self->craftsman = $craftsman;
        $self->issueAnalysis = $issueAnalysis;

        return $self;
    }

    public function getCraftsman(): Craftsman
    {
        return $this->craftsman;
    }

    public function getIssueAnalysis(): CraftsmanIssueAnalysis
    {
        return $this->issueAnalysis;
    }

    public function getNextDeadline(): ?\DateTimeImmutable
    {
        return $this->nextDeadline;
    }

    public function setNextDeadline(?\DateTimeImmutable $nextDeadline): void
    {
        $this->nextDeadline = $nextDeadline;
    }

    public function getLastEmailReceived(): ?\DateTimeImmutable
    {
        return $this->lastEmailReceived;
    }

    public function setLastEmailReceived(?\DateTimeImmutable $lastEmailReceived): void
    {
        $this->lastEmailReceived = $lastEmailReceived;
    }

    public function getLastVisitOnline(): ?\DateTimeImmutable
    {
        return $this->lastVisitOnline;
    }

    public function setLastVisitOnline(?\DateTimeImmutable $lastVisitOnline): void
    {
        $this->lastVisitOnline = $lastVisitOnline;
    }

    public function getLastIssueResolved(): ?\DateTimeImmutable
    {
        return $this->lastIssueResolved;
    }

    public function setLastIssueResolved(?\DateTimeImmutable $lastIssueResolved): void
    {
        $this->lastIssueResolved = $lastIssueResolved;
    }
}
