<?php

namespace App\Service;

use App\Entity\Craftsman;
use App\Helper\DateTimeFormatter;
use App\Service\Analysis\CraftsmanAnalysis;
use App\Service\Analysis\CraftsmanIssueAnalysis;
use App\Service\Analysis\Database\CraftsmanService;
use App\Service\Analysis\Database\IssueService;
use App\Service\Analysis\IssueAnalysis;
use App\Service\Interfaces\AnalysisServiceInterface;
use Doctrine\ORM\QueryBuilder;

readonly class AnalysisService implements AnalysisServiceInterface
{
    public function __construct(private IssueService $issueService, private CraftsmanService $craftsmanService)
    {
    }

    /**
     * returns highest date first.
     *
     * @return IssueAnalysis[]
     */
    public function createIssueAnalysisByTime(string $rootAlias, QueryBuilder $queryBuilder, \DateTimeImmutable $lastPeriodEnd, \DateInterval $stepSize, int $stepCount, string $dateFormat = DateTimeFormatter::ISO_DATE_FORMAT): array
    {
        $issueAnalysis = $this->createIssueAnalysis($rootAlias, $queryBuilder);

        $nextPeriod = (clone $lastPeriodEnd)->add($stepSize);
        $nextPeriodFormat = $nextPeriod->format($dateFormat);
        $issueAnalysisByTime = [$nextPeriodFormat => $issueAnalysis];

        $backtrackDate = $this->getBacktrackDate($lastPeriodEnd, $stepSize, $stepCount);
        $stateChangeIssues = $this->issueService->getStateChangeIssues($queryBuilder, $rootAlias, $backtrackDate);

        $current = clone $lastPeriodEnd;
        while ($stepCount-- > 0) {
            $currentIssueAnalysis = clone $issueAnalysis;

            foreach ($stateChangeIssues as $issue) {
                $registeredAt = $issue['registeredAt'];
                $resolvedAt = $issue['resolvedAt'];
                $closedAt = $issue['closedAt'];

                $this->applyDeltaToIssueCountAnalysis($currentIssueAnalysis, $current, $registeredAt, $resolvedAt, $closedAt);
            }

            $currentFormat = $current->format($dateFormat);
            $issueAnalysisByTime[$currentFormat] = $currentIssueAnalysis;

            $current = $current->sub($stepSize);
        }

        return $issueAnalysisByTime;
    }

    public function createIssueAnalysis(string $rootAlias, QueryBuilder $queryBuilder): IssueAnalysis
    {
        $issueSummary = new IssueAnalysis();

        $newCount = $this->issueService->countNewIssues($rootAlias, $queryBuilder);
        $issueSummary->setNewCount($newCount);

        $openCount = $this->issueService->countOpenIssues($rootAlias, $queryBuilder);
        $issueSummary->setOpenCount($openCount);

        $inspectableCount = $this->issueService->countInspectableIssues($rootAlias, $queryBuilder);
        $issueSummary->setInspectableCount($inspectableCount);

        $closedCount = $this->issueService->countClosedIssues($rootAlias, $queryBuilder);
        $issueSummary->setClosedCount($closedCount);

        return $issueSummary;
    }

    /**
     * @param Craftsman[] $craftsmen
     *
     * @return CraftsmanAnalysis[]
     */
    public function createCraftsmanAnalysis(array $craftsmen): array
    {
        $craftsmanIssueAnalysisByCraftsman = [];
        $craftsmenDictionary = [];
        foreach ($craftsmen as $craftsman) {
            $craftsmanIssueAnalysisByCraftsman[$craftsman->getId()] = new CraftsmanIssueAnalysis();
            $craftsmenDictionary[$craftsman->getId()] = $craftsman;
        }

        $this->craftsmanService->findIssueAnalysisByCraftsman($craftsmen, $craftsmanIssueAnalysisByCraftsman);

        $craftsmanAnalysisDictionary = [];
        foreach ($craftsmanIssueAnalysisByCraftsman as $craftsmanId => $craftsmanIssueAnalysis) {
            $craftsmanAnalysisDictionary[$craftsmanId] = CraftsmanAnalysis::create($craftsmenDictionary[$craftsmanId], $craftsmanIssueAnalysis);
        }

        $this->craftsmanService->findNextDeadline($craftsmen, $craftsmanAnalysisDictionary);
        $this->craftsmanService->findLastIssueResolved($craftsmen, $craftsmanAnalysisDictionary);
        $this->findLastActivity($craftsmen, $craftsmanAnalysisDictionary);

        return array_values($craftsmanAnalysisDictionary);
    }

    /**
     * @param Craftsman[] $craftsmen
     * @param CraftsmanAnalysis[] $craftsmanAnalysisDictionary
     */
    private function findLastActivity(array $craftsmen, array $craftsmanAnalysisDictionary): void
    {
        foreach ($craftsmen as $craftsman) {
            $craftsmanAnalysisDictionary[$craftsman->getId()]->setLastEmailReceived($craftsman->getLastEmailReceived());
            $craftsmanAnalysisDictionary[$craftsman->getId()]->setLastVisitOnline($craftsman->getLastVisitOnline());
        }
    }

    private function applyDeltaToIssueCountAnalysis(IssueAnalysis $issueCountAnalysis, \DateTimeImmutable $timestamp, ?\DateTimeImmutable $registeredAt, ?\DateTimeImmutable $resolvedAt, ?\DateTimeImmutable $closedAt): void
    {
        if ($closedAt instanceof \DateTimeImmutable) {
            // summary counted issue at "completed"
            if ($closedAt > $timestamp) {
                $issueCountAnalysis->setClosedCount($issueCountAnalysis->getClosedCount() - 1);
                if (null != $resolvedAt && $resolvedAt <= $timestamp) {
                    $issueCountAnalysis->setInspectableCount($issueCountAnalysis->getInspectableCount() + 1);
                } elseif (null != $registeredAt && $registeredAt <= $timestamp) {
                    $issueCountAnalysis->setOpenCount($issueCountAnalysis->getOpenCount() + 1);
                }
            }
        } elseif ($resolvedAt instanceof \DateTimeImmutable) {
            // summary counted issue at "resolved"
            if ($resolvedAt > $timestamp) {
                $issueCountAnalysis->setInspectableCount($issueCountAnalysis->getInspectableCount() - 1);
                if (null != $registeredAt && $registeredAt <= $timestamp) {
                    $issueCountAnalysis->setOpenCount($issueCountAnalysis->getOpenCount() + 1);
                }
            }
        } elseif ($registeredAt > $timestamp) {
            // summary counted issue at "open"
            $issueCountAnalysis->setOpenCount($issueCountAnalysis->getOpenCount() - 1);
        }
    }

    private function getBacktrackDate(\DateTimeImmutable $lastPeriodEnd, \DateInterval $stepSize, int $stepCount): \DateTimeImmutable
    {
        $backtrackDate = clone $lastPeriodEnd;
        $currentStep = $stepCount;
        while ($currentStep-- > 0) {
            $backtrackDate = $backtrackDate->sub($stepSize);
        }

        return $backtrackDate;
    }
}
