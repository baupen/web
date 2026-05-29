<?php

namespace App\Api\Dto\CraftsmanStatistics;

use App\Service\Analysis\CraftsmanIssueAnalysis;
use Symfony\Component\Serializer\Attribute\Groups;

class IssueSummaryDto
{
    #[Groups(['craftsman-statistics:read'])]
    private int $openCount;

    #[Groups(['craftsman-statistics:read'])]
    private int $inspectableCount;

    #[Groups(['craftsman-statistics:read'])]
    private int $closedCount;

    public function __construct(int $openCount, int $inspectableCount, int $closedCount)
    {
        $this->openCount = $openCount;
        $this->inspectableCount = $inspectableCount;
        $this->closedCount = $closedCount;
    }

    public static function create(CraftsmanIssueAnalysis $craftsmanIssueAnalysis): self
    {
        return new self(
            $craftsmanIssueAnalysis->getOpenCount(),
            $craftsmanIssueAnalysis->getInspectableCount(),
            $craftsmanIssueAnalysis->getClosedCount()
        );
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
