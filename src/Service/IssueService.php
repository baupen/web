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

use App\Entity\Issue;
use App\Helper\DateTimeFormatter;
use App\Service\Interfaces\IssueServiceInterface;
use App\Service\Issue\Summary;
use App\Service\Issue\SummaryWithDate;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class IssueService implements IssueServiceInterface
{
    /**
     * @var ManagerRegistry
     */
    private $manager;

    /**
     * CraftsmanService constructor.
     */
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    public function createTimeseries(string $rootAlias, QueryBuilder $queryBuilder, \DateTime $lastPeriodEnd, \DateInterval $stepSize, int $stepCount): array
    {
        $summary = $this->createSummary($rootAlias, $queryBuilder);

        $issueRepository = $this->manager->getRepository(Issue::class);
        $backtrackDate = $this->getBacktrackDate($lastPeriodEnd, $stepSize, $stepCount);
        $stateChangeIssues = $issueRepository->getStateChangeIssues($queryBuilder, $rootAlias, $backtrackDate);

        $deltas = $this->createDeltaSummaries($summary, $stateChangeIssues, $lastPeriodEnd, $stepSize, $stepCount);

        $nextPeriod = (clone $lastPeriodEnd)->add($stepSize);
        $summaryWithDate = new SummaryWithDate();
        $summaryWithDate->setDate($nextPeriod->format(DateTimeFormatter::ISO_DATE_FORMAT));
        $summaryWithDate->writeFrom($summary);

        return [...$deltas, $summaryWithDate];
    }

    public function createSummary(string $rootAlias, QueryBuilder $queryBuilder): Summary
    {
        $issueSummary = new Summary();

        $issueRepository = $this->manager->getRepository(Issue::class);

        $newCount = $this->filterAndCount($rootAlias, $queryBuilder, [$issueRepository, 'filterNewIssues']);
        $issueSummary->setNewCount($newCount);

        $openCount = $this->filterAndCount($rootAlias, $queryBuilder, [$issueRepository, 'filterOpenIssues']);
        $issueSummary->setOpenCount($openCount);

        $inspectableCount = $this->filterAndCount($rootAlias, $queryBuilder, [$issueRepository, 'filterInspectableIssues']);
        $issueSummary->setInspectableCount($inspectableCount);

        $closedCount = $this->filterAndCount($rootAlias, $queryBuilder, [$issueRepository, 'filterClosedIssues']);
        $issueSummary->setClosedCount($closedCount);

        return $issueSummary;
    }

    /**
     * @return Summary[]
     */
    public function createDeltaSummaries(Summary $summary, array $stateChangeIssues, \DateTime $lastPeriodEnd, \DateInterval $stepSize = null, int $stepCount = 0): array
    {
        $countByDayDescending = [];
        $current = $lastPeriodEnd;
        while ($stepCount-- > 0) {
            $countByDayDescending[] = [clone $current, 0, 0, 0];
            $current->sub($stepSize);
        }

        foreach ($stateChangeIssues as $issue) {
            $registeredAt = $issue['registeredAt'];
            $resolvedAt = $issue['resolvedAt'];
            $closedAt = $issue['closedAt'];

            if (null !== $closedAt) {
                // summary counted issue at "completed"
                foreach ($countByDayDescending as [$day, &$openCountCorrection, &$inspectableCountCorrection, &$closedCountCorrection]) {
                    if ($closedAt > $day) {
                        --$closedCountCorrection;
                        if (null != $resolvedAt && $resolvedAt <= $day) {
                            ++$inspectableCountCorrection;
                        } elseif (null != $registeredAt && $registeredAt <= $day) {
                            ++$openCountCorrection;
                        }
                    }
                }
            } elseif (null !== $resolvedAt) {
                // summary counted issue at "resolved"
                foreach ($countByDayDescending as [$day, &$openCountCorrection, &$inspectableCountCorrection]) {
                    if ($resolvedAt > $day) {
                        --$inspectableCountCorrection;
                        if (null != $registeredAt && $registeredAt <= $day) {
                            ++$openCountCorrection;
                        }
                    }
                }
            } else {
                // summary counted issue at "open"
                foreach ($countByDayDescending as [$day, &$openCountCorrection]) {
                    if ($registeredAt > $day) {
                        --$openCountCorrection;
                    }
                }
            }

            unset($openCountCorrection);
            unset($inspectableCountCorrection);
            unset($closedCountCorrection);
        }

        $deltaSummaries = [];
        foreach ($countByDayDescending as [$day, $openCountCorrection, $inspectableCountCorrection, $closedCountCorrection]) {
            $currentSummary = new SummaryWithDate();
            $currentSummary->setDate($day->format(DateTimeFormatter::ISO_DATE_FORMAT));
            $currentSummary->setOpenCount($summary->getOpenCount() + $openCountCorrection);
            $currentSummary->setInspectableCount($summary->getInspectableCount() + $inspectableCountCorrection);
            $currentSummary->setClosedCount($summary->getClosedCount() + $closedCountCorrection);

            $deltaSummaries[] = $currentSummary;
        }

        // return lowest date first
        return array_reverse($deltaSummaries);
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

    private function filterAndCount(string $rootAlias, QueryBuilder $builder, callable $filter): int
    {
        $filteredBuilder = $filter($rootAlias, clone $builder);

        return $this->countResult($rootAlias, $filteredBuilder);
    }

    private function countResult(string $rootAlias, QueryBuilder $builder): int
    {
        return $builder->select('count('.$rootAlias.')')
            ->getQuery()->getSingleScalarResult();
    }
}
