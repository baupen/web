<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity;

use App\Service\Analysis\CraftsmanAnalysis;

class CraftsmanStatistics
{
    /**
     * @var string
     */
    private $craftsman;

    /**
     * @var IssueSummary
     */
    private $issueSummary;

    /**
     * @var int
     */
    public $issueUnreadCount;

    /**
     * @var int
     */
    public $issueOverdueCount;

    /**
     * @var \DateTime|null
     */
    public $nextDeadline;

    /**
     * @var \DateTime|null
     */
    public $lastEmailReceived;

    /**
     * @var \DateTime|null
     */
    public $lastVisitOnline;

    /**
     * @var \DateTime|null
     */
    public $lastIssueResolved;

    /**
     * CraftsmanStatistics constructor.
     */
    public static function createFromCraftsmanAnalysis(CraftsmanAnalysis $craftsmanAnalysis, string $craftsmanIri)
    {
        $self = new self();

        $self->craftsman = $craftsmanIri;
        $self->issueSummary = IssueSummary::createFromCraftsmanIssueAnalysis($craftsmanAnalysis->getIssueAnalysis());
        $self->issueUnreadCount = $craftsmanAnalysis->getIssueAnalysis()->getUnreadCount();
        $self->issueOverdueCount = $craftsmanAnalysis->getIssueAnalysis()->getOverdueCount();
        $self->nextDeadline = $craftsmanAnalysis->getNextDeadline();
        $self->lastEmailReceived = $craftsmanAnalysis->getLastEmailReceived();
        $self->lastVisitOnline = $craftsmanAnalysis->getLastVisitOnline();
        $self->lastIssueResolved = $craftsmanAnalysis->getLastIssueResolved();

        return $self;
    }

    public function getCraftsman(): string
    {
        return $this->craftsman;
    }

    public function setCraftsman(string $craftsman): void
    {
        $this->craftsman = $craftsman;
    }

    public function getIssueSummary(): IssueSummary
    {
        return $this->issueSummary;
    }
}
