<?php

namespace App\Api\Entity;

use App\Service\Analysis\IssueAnalysis;
use Symfony\Component\Serializer\Annotation\Groups;

class IssueSummaryWithDate extends IssueSummary
{
    #[Groups(['issue-read'])]
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
