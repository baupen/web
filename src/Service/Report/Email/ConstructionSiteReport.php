<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Email;

use App\Entity\ConstructionSite;

class ConstructionSiteReport
{
    use IssueCountTrait;
    use IssueCountDeltaTrait;

    /**
     * @var ConstructionSite
     */
    private $constructionSite;

    /**
     * @var \DateTime
     */
    private $comparisonTimestamp;

    /**
     * @var CraftsmanDeltaReport[]
     */
    private $craftsmanDeltaReports;

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
