<?php

namespace App\Api\Dto;

use App\Service\Analysis\IssueAnalysis;
use Symfony\Component\Serializer\Attribute\Groups;

class IssueSummaryDatedDto extends IssueSummaryDto
{
    #[Groups(['issue-summary:read'])]
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
}
