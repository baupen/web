<?php

namespace App\Service\Report\Email;

use App\Entity\ConstructionSite;

class ConstructionSiteReport
{
    use IssueCountTrait;
    use IssueCountDeltaTrait;

    private ConstructionSite $constructionSite;

    private \DateTimeImmutable $comparisonTimestamp;

    /**
     * @var CraftsmanDeltaReport[]
     */
    private array $craftsmanDeltaReports;

    /**
     * ConstructionSiteReport constructor.
     *
     * @param CraftsmanDeltaReport[] $craftsmanDeltaReports
     */
    public function __construct(ConstructionSite $constructionSite, \DateTimeImmutable $comparisonTimestamp, array $craftsmanDeltaReports)
    {
        $this->constructionSite = $constructionSite;
        $this->comparisonTimestamp = $comparisonTimestamp;
        $this->craftsmanDeltaReports = $craftsmanDeltaReports;

        foreach ($craftsmanDeltaReports as $craftsmanDeltaReport) {
            $this->addIssueCount($craftsmanDeltaReport);
            $this->addIssueCountDelta($craftsmanDeltaReport);
        }
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function getComparisonTimestamp(): \DateTimeImmutable
    {
        return $this->comparisonTimestamp;
    }

    /**
     * @return CraftsmanDeltaReport[]
     */
    public function getCraftsmanDeltaReports(): array
    {
        return $this->craftsmanDeltaReports;
    }
}
