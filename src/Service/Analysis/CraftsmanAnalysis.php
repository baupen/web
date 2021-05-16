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

class CraftsmanAnalysis
{
    private CraftsmanIssueAnalysis $issueAnalysis;

    private ?\DateTime $nextDeadline = null;

    private ?\DateTime $lastEmailReceived = null;

    private ?\DateTime $lastVisitOnline = null;

    private ?\DateTime $lastIssueResolved = null;

    public static function createWithIssueAnalysis(CraftsmanIssueAnalysis $issueAnalysis)
    {
        $self = new self();

        $self->issueAnalysis = $issueAnalysis;

        return $self;
    }

    public function getIssueAnalysis(): CraftsmanIssueAnalysis
    {
        return $this->issueAnalysis;
    }

    public function setIssueAnalysis(CraftsmanIssueAnalysis $issueAnalysis): void
    {
        $this->issueAnalysis = $issueAnalysis;
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
