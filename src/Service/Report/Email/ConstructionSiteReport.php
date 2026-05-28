<?php

namespace App\Service\Report\Email;

use App\Entity\ConstructionSite;

class ConstructionSiteReport
{
    use IssueCountTrait;
    use IssueCountDeltaTrait;

    private ConstructionSite $constructionSite;

    private \DateTime $comparisonTimestamp;

    /**
     * @var CraftsmanDeltaReport[]
     */
    private array $craftsmanDeltaReports;

    /**
     * ConstructionSiteReport constructor.
     *
     * @param CraftsmanDeltaReport[] $craftsmanDeltaReports
     */
    public function __construct(ConstructionSite $constructionSite, \DateTime $comparisonTimestamp, array $craftsmanDeltaReports)
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

    public function getComparisonTimestamp(): \DateTime
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
