<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Service\Analysis\Database\CraftsmanService;
use App\Service\Analysis\Database\IssueService;
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Report\Email\ConstructionSiteReport;
use App\Service\Report\Email\CraftsmanDeltaReport;
use App\Service\Report\Email\IssueCountDeltaTrait;
use App\Service\Report\Pdf\PdfService;
use App\Service\Report\Pdf\ReportElements;

class ReportService implements ReportServiceInterface
{
    /**
     * @var PdfService
     */
    private $pdfService;

    /**
     * @var IssueService
     */
    private $issueService;

    /**
     * @var CraftsmanService
     */
    private $craftsmanService;

    /**
     * ReportService constructor.
     */
    public function __construct(PdfService $pdfService, IssueService $issueService, CraftsmanService $craftsmanService)
    {
        $this->pdfService = $pdfService;
        $this->issueService = $issueService;
        $this->craftsmanService = $craftsmanService;
    }

    public function generatePdfReport(array $issues, Filter $filter, ReportElements $reportElements, ?string $author = null): string
    {
        return $this->pdfService->generatePdfReport($issues, $filter, $reportElements, $author);
    }

    public function createConstructionSiteReport(ConstructionSite $constructionSite, \DateTime $comparisonTimestamp): ConstructionSiteReport
    {
        $craftsmanDeltaReportByCraftsman = [];
        $relevantCraftsmen = [];
        foreach ($constructionSite->getCraftsmen() as $craftsman) {
            if (!$craftsman->getIsDeleted()) {
                $relevantCraftsmen[] = $craftsman;
                $craftsmanDeltaReportByCraftsman[$craftsman->getId()] = new CraftsmanDeltaReport($craftsman);
            }
        }

        $this->craftsmanService->findIssueCountByCraftsman($relevantCraftsmen, $craftsmanDeltaReportByCraftsman);

        $rootAlias = 'i';
        $queryBuilder = $this->craftsmanService->getCraftsmanIssuesQueryBuilder($rootAlias, $relevantCraftsmen)->addSelect('identity('.$rootAlias.'.craftsman) AS craftsman');
        $stateChangeIssues = $this->issueService->getStateChangeIssues($queryBuilder, $rootAlias, $comparisonTimestamp);

        foreach ($stateChangeIssues as $stateChangeIssue) {
            $registeredAt = $stateChangeIssue['registeredAt'];
            $resolvedAt = $stateChangeIssue['resolvedAt'];
            $closedAt = $stateChangeIssue['closedAt'];
            $craftsman = $stateChangeIssue['craftsman'];

            $craftsmanDeltaReport = $craftsmanDeltaReportByCraftsman[$craftsman];

            $this->fillIssueCountDelta($craftsmanDeltaReport, $comparisonTimestamp, $registeredAt, $resolvedAt, $closedAt);
        }

        usort($craftsmanDeltaReportByCraftsman, function (CraftsmanDeltaReport $a, CraftsmanDeltaReport $b) {return $a->getCraftsman()->sort($b->getCraftsman()); });

        return new ConstructionSiteReport($constructionSite, $comparisonTimestamp, array_values($craftsmanDeltaReportByCraftsman));
    }

    /**
     * @param IssueCountDeltaTrait $issueCountDelta
     */
    private function fillIssueCountDelta($issueCountDelta, \DateTime $timestamp, ?\DateTime $registeredAt, ?\DateTime $resolvedAt, ?\DateTime $closedAt)
    {
        if ($closedAt > $timestamp) {
            // issue newly "closed"
            $issueCountDelta->setClosedCountDelta($issueCountDelta->getClosedCountDelta() + 1);
        }

        if ($resolvedAt > $timestamp) {
            // issue newly "resolved"
            $issueCountDelta->setResolvedCountDelta($issueCountDelta->getResolvedCountDelta() + 1);
        }

        if (null !== $registeredAt) {
            if ($registeredAt > $timestamp && null === $closedAt && null === $resolvedAt) {
                // issue newly "registered"
                $issueCountDelta->setOpenCountDelta($issueCountDelta->getOpenCountDelta() + 1);
            } elseif ($registeredAt < $timestamp && ($closedAt > $timestamp || $resolvedAt > $timestamp)) {
                // issue has been resolved / closed, hence no longer open
                $issueCountDelta->setOpenCountDelta($issueCountDelta->getOpenCountDelta() - 1);
            }
        }
    }
}
