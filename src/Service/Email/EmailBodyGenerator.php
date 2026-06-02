<?php

namespace App\Service\Email;

use App\Helper\DateTimeFormatter;
use App\Service\Report\Email\ConstructionSiteReport;
use App\Service\Report\Email\CraftsmanDeltaReport;
use App\Service\Report\Email\CraftsmanReport;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class EmailBodyGenerator
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function fromConstructionSiteReports(array $constructionSiteReports): array
    {
        $normalizedConstructionSiteReports = [];
        foreach ($constructionSiteReports as $constructionSiteReport) {
            $normalizedConstructionSiteReports[] = $this->fromConstructionSiteReport($constructionSiteReport);
        }

        return [
            'type' => 'construction_site_reports',
            'constructionSiteReports' => $normalizedConstructionSiteReports,
        ];
    }

    public function fromCraftsmanReport(CraftsmanReport $craftsmanReport): array
    {
        $normalizedCraftsmanReport = [];
        if ($craftsmanReport->getComparisonTimestamp()) {
            $normalizedCraftsmanReport['comparisonTimeStamp'] = $craftsmanReport->getComparisonTimestamp()->format(DateTimeFormatter::DATE_TIME_FORMAT);
        }

        $issueCountProperties = $this->getIssueCountProperties($craftsmanReport);
        $issueCountDeltaProperties = $this->getIssueCountDeltaProperties($craftsmanReport);

        return array_merge($normalizedCraftsmanReport, $issueCountProperties, $issueCountDeltaProperties);
    }

    private function fromConstructionSiteReport(ConstructionSiteReport $constructionSiteReport): array
    {
        $constructionManagers = [];
        $constructionSite = $constructionSiteReport->getConstructionSite();
        foreach ($constructionSite->getConstructionManagers() as $constructionManager) {
            $constructionManagers[] = $constructionManager->getName();
        }

        $dashboardUrl = $this->urlGenerator->generate('construction_site_dashboard', ['constructionSite' => $constructionSite->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $craftsmanReports = [];
        foreach ($constructionSiteReport->getCraftsmanDeltaReports() as $craftsmanDeltaReport) {
            $craftsmanReport = [
                'company' => $craftsmanDeltaReport->getCraftsman()->getCompany(),
                'trade' => $craftsmanDeltaReport->getCraftsman()->getTrade(),
            ];

            $issueCountProperties = $this->getIssueCountProperties($craftsmanDeltaReport);
            $issueCountDeltaProperties = $this->getIssueCountDeltaProperties($craftsmanDeltaReport);
            $craftsmanReports[] = array_merge($craftsmanReport, $issueCountProperties, $issueCountDeltaProperties);
        }

        // sort craftsmen
        usort($craftsmanReports, function (array $a, array $b): int {
            return strcmp($a['company'], $b['company']);
        });

        $normalizedConstructionSiteReport = [
            'name' => $constructionSite->getName(),
            'constructionManagers' => $constructionManagers,
            'dashboardUrl' => $dashboardUrl,
            'craftsmanReports' => $craftsmanReports,
        ];
        $issueCountProperties = $this->getIssueCountProperties($constructionSiteReport);
        $issueCountDeltaProperties = $this->getIssueCountDeltaProperties($constructionSiteReport);

        return array_merge($normalizedConstructionSiteReport, $issueCountProperties, $issueCountDeltaProperties);
    }

    private function getIssueCountProperties(ConstructionSiteReport|CraftsmanDeltaReport $issueCountTrait): array
    {
        return [
            'openCount' => $issueCountTrait->getOpenCount(),
            'resolvedCount' => $issueCountTrait->getResolvedCount(),
            'closedCount' => $issueCountTrait->getClosedCount(),
        ];
    }

    private function getIssueCountDeltaProperties(ConstructionSiteReport|CraftsmanDeltaReport $issueCountDeltaTrait): array
    {
        return [
            'openCountDelta' => $issueCountDeltaTrait->getOpenCountDelta(),
            'resolvedCountDelta' => $issueCountDeltaTrait->getResolvedCountDelta(),
            'closedCountDelta' => $issueCountDeltaTrait->getClosedCountDelta(),
        ];
    }
}
