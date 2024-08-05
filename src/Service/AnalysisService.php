<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Craftsman;
use App\Helper\DateTimeFormatter;
use App\Service\Analysis\CraftsmanAnalysis;
use App\Service\Analysis\CraftsmanIssueAnalysis;
use App\Service\Analysis\Database\CraftsmanService;
use App\Service\Analysis\Database\IssueService;
use App\Service\Analysis\IssueAnalysis;
use App\Service\Analysis\IssueCountAnalysisTrait;
use App\Service\Interfaces\AnalysisServiceInterface;
use Doctrine\ORM\QueryBuilder;

class AnalysisService implements AnalysisServiceInterface
{
    private IssueService $issueService;

    private CraftsmanService $craftsmanService;

    /**
     * CraftsmanService constructor.
     */
    public function __construct(IssueService $issueService, CraftsmanService $craftsmanService)
    {
        $this->issueService = $issueService;
        $this->craftsmanService = $craftsmanService;
    }

    /**
     * returns highest date first.
     *
     * @return IssueAnalysis[]
     */
    public function createIssueAnalysisByTime(string $rootAlias, QueryBuilder $queryBuilder, \DateTime $lastPeriodEnd, \DateInterval $stepSize, int $stepCount, string $dateFormat = DateTimeFormatter::ISO_DATE_FORMAT): array
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

            $current->sub($stepSize);
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
    public function createCraftsmanAnalysisByCraftsman(array $craftsmen): array
    {
        $craftsmanIssueAnalysisByCraftsman = [];
        foreach ($craftsmen as $craftsman) {
            $craftsmanIssueAnalysisByCraftsman[$craftsman->getId()] = new CraftsmanIssueAnalysis();
        }

        $this->craftsmanService->findIssueAnalysisByCraftsman($craftsmen, $craftsmanIssueAnalysisByCraftsman);

        $craftsmanAnalysisDictionary = [];
        foreach ($craftsmanIssueAnalysisByCraftsman as $craftsmanId => $craftsmanIssueAnalysis) {
            $craftsmanAnalysisDictionary[$craftsmanId] = CraftsmanAnalysis::createWithIssueAnalysis($craftsmanIssueAnalysis);
        }

        $this->craftsmanService->findNextDeadline($craftsmen, $craftsmanAnalysisDictionary);
        $this->craftsmanService->findLastIssueResolved($craftsmen, $craftsmanAnalysisDictionary);
        $this->findLastActivity($craftsmen, $craftsmanAnalysisDictionary);

        return $craftsmanAnalysisDictionary;
    }

    /**
     * @param Craftsman[] $craftsmen
     */
    private function findLastActivity(array $craftsmen, array $craftsmanAnalysisDictionary)
    {
        foreach ($craftsmen as $craftsman) {
            $craftsmanAnalysisDictionary[$craftsman->getId()]->setLastEmailReceived($craftsman->getLastEmailReceived());
            $craftsmanAnalysisDictionary[$craftsman->getId()]->setLastVisitOnline($craftsman->getLastVisitOnline());
        }
    }

    /**
     * @param IssueCountAnalysisTrait $issueCountAnalysis
     */
    private function applyDeltaToIssueCountAnalysis($issueCountAnalysis, \DateTime $timestamp, ?\DateTime $registeredAt, ?\DateTime $resolvedAt, ?\DateTime $closedAt)
    {
        if ($closedAt instanceof \DateTime) {
            // summary counted issue at "completed"
            if ($closedAt > $timestamp) {
                $issueCountAnalysis->setClosedCount($issueCountAnalysis->getClosedCount() - 1);
                if (null != $resolvedAt && $resolvedAt <= $timestamp) {
                    $issueCountAnalysis->setInspectableCount($issueCountAnalysis->getInspectableCount() + 1);
                } elseif (null != $registeredAt && $registeredAt <= $timestamp) {
                    $issueCountAnalysis->setOpenCount($issueCountAnalysis->getOpenCount() + 1);
                }
            }
        } elseif ($resolvedAt instanceof \DateTime) {
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

    private function getBacktrackDate(\DateTime $lastPeriodEnd, \DateInterval $stepSize, int $stepCount): \DateTime
    {
        $backtrackDate = clone $lastPeriodEnd;
        $currentStep = $stepCount;
        while ($currentStep-- > 0) {
            $backtrackDate->sub($stepSize);
        }

        return $backtrackDate;
    }
}
