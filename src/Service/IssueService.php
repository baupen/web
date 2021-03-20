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

    public function getTimeseries(string $rootAlias, QueryBuilder $queryBuilder, \DateTime $backtrackDate, \DateInterval $stepSize): array
    {
        /** @var Summary $summary */
        $summary = $this->createSummary($rootAlias, $queryBuilder);

        $stateChangeIssues = $this->getRecentStateChangesOfIssues($queryBuilder, $rootAlias, $backtrackDate);

        $countByDayDescending = [];
        $today = new \DateTime('today');
        $day = $stepSize;
        while ($today >= $backtrackDate) {
            $countByDayDescending[] = [clone $today, 0, 0, 0];
            $today->sub($day);
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

        $summaries = [];
        array_unshift($countByDayDescending, [new \DateTime('tomorrow'), 0, 0, 0]); // include today
        foreach ($countByDayDescending as [$day, $openCountCorrection, $inspectableCountCorrection, $closedCountCorrection]) {
            $currentSummary = new SummaryWithDate();
            $currentSummary->setDate($day->format(DateTimeFormatter::ISO_DATE_FORMAT));
            $currentSummary->setOpenCount($summary->getOpenCount() + $openCountCorrection);
            $currentSummary->setInspectableCount($summary->getInspectableCount() + $inspectableCountCorrection);
            $currentSummary->setClosedCount($summary->getClosedCount() + $closedCountCorrection);

            $summaries[] = $currentSummary;
        }

        // return earliest first
        return array_reverse($summaries);
    }

    /**
     * @param $rootAlias
     *
     * @return \DateTime[][]
     */
    private function getRecentStateChangesOfIssues(QueryBuilder $queryBuilder, $rootAlias, \DateTime $backtrackDate): array
    {
        $queryBuilder->addSelect($rootAlias.'.registeredAt registeredAt, '.$rootAlias.'.resolvedAt resolvedAt, '.$rootAlias.'.closedAt closedAt');
        $queryBuilder
            ->andWhere($rootAlias.'.registeredAt > :backtrack_1 OR '.$rootAlias.'.resolvedAt > :backtrack_2 OR '.$rootAlias.'.closedAt > :backtrack_3')
            ->setParameter(':backtrack_1', $backtrackDate)
            ->setParameter(':backtrack_2', $backtrackDate)
            ->setParameter(':backtrack_3', $backtrackDate);

        return $queryBuilder->getQuery()->getResult();
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
