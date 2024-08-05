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

use App\Service\Analysis\IssueAnalysis;
use Symfony\Component\Serializer\Annotation\Groups;

class IssueSummaryWithDate extends IssueSummary
{
    /**
     * @Groups({"issue-read"})
     */
    private string $date;

    public static function createFromIssueAnalysisWithDate(IssueAnalysis $issueAnalysis, string $date): self
    {
        $self = new self();

        $self->writeFromIssueAnalysis($issueAnalysis);
        $self->date = $date;

        return $self;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDate(string $date): void
    {
        $this->date = $date;
    }
}
