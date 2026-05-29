<?php

namespace App\Service\Analysis;

use App\Entity\Craftsman;

class CraftsmanAnalysis
{
    private Craftsman $craftsman;

    private CraftsmanIssueAnalysis $issueAnalysis;

    private ?\DateTime $nextDeadline = null;

    private ?\DateTime $lastEmailReceived = null;

    private ?\DateTime $lastVisitOnline = null;

    private ?\DateTime $lastIssueResolved = null;

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

    public function getNextDeadline(): ?\DateTime
    {
        return $this->nextDeadline;
    }

    public function setNextDeadline(?\DateTime $nextDeadline): void
    {
        $this->nextDeadline = $nextDeadline;
    }

    public function getLastEmailReceived(): ?\DateTime
    {
        return $this->lastEmailReceived;
    }

    public function setLastEmailReceived(?\DateTime $lastEmailReceived): void
    {
        $this->lastEmailReceived = $lastEmailReceived;
    }

    public function getLastVisitOnline(): ?\DateTime
    {
        return $this->lastVisitOnline;
    }

    public function setLastVisitOnline(?\DateTime $lastVisitOnline): void
    {
        $this->lastVisitOnline = $lastVisitOnline;
    }

    public function getLastIssueResolved(): ?\DateTime
    {
        return $this->lastIssueResolved;
    }

    public function setLastIssueResolved(?\DateTime $lastIssueResolved): void
    {
        $this->lastIssueResolved = $lastIssueResolved;
    }
}
