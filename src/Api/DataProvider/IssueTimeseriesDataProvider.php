<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\DataProvider;

use App\Api\DataProvider\Base\NoPaginationDataProvider;
use App\Api\Entity\IssueSummaryWithDate;
use App\Entity\Issue;
use App\Helper\DateTimeFormatter;
use DateInterval;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class IssueTimeseriesDataProvider extends NoPaginationDataProvider
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ManagerRegistry
     */
    private $manager;

    public function __construct(SerializerInterface $serializer, ManagerRegistry $managerRegistry, iterable $collectionExtensions = [])
    {
        parent::__construct($managerRegistry, $collectionExtensions);
        $this->serializer = $serializer;
        $this->manager = $managerRegistry;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Issue::class === $resourceClass && 'get_timeseries' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $queryBuilder = $this->getCollectionQueryBuilerWithoutPagination($resourceClass, $operationName, $context);
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $summary = $this->manager->getRepository(Issue::class)->createSummary($rootAlias, $queryBuilder);

        $backtrackDate = new DateTime('today - 7 days');
        $stateChangeIssues = $this->getRecentStateChangesOfIssues($queryBuilder, $rootAlias, $backtrackDate);

        $countByDayDescending = [];
        $currentDate = new DateTime('today');
        $day = new DateInterval('P1D');
        while ($currentDate >= $backtrackDate) {
            $countByDayDescending[] = [clone $currentDate, 0, 0, 0];
            $currentDate->sub($day);
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
                        if (null != $resolvedAt && $resolvedAt < $day) {
                            ++$inspectableCountCorrection;
                        } elseif (null != $registeredAt && $registeredAt < $day) {
                            ++$openCountCorrection;
                        }
                    }
                }
            } elseif (null !== $resolvedAt) {
                // summary counted issue at "resolved"
                foreach ($countByDayDescending as [$day, &$openCountCorrection, &$inspectableCountCorrection]) {
                    if ($resolvedAt > $day) {
                        --$inspectableCountCorrection;
                        if (null != $registeredAt && $registeredAt < $day) {
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
        }

        $summaries = [];
        foreach ($countByDayDescending as [$day, $openCountCorrection, $inspectableCountCorrection, $closedCountCorrection]) {
            $currentSummary = new IssueSummaryWithDate();
            $currentSummary->setDate($day->format(DateTimeFormatter::ISO_DATE_FORMAT));
            $currentSummary->setOpenCount($summary->getOpenCount() + $openCountCorrection);
            $currentSummary->setInspectableCount($summary->getInspectableCount() + $inspectableCountCorrection);
            $currentSummary->setClosedCount($summary->getClosedCount() + $closedCountCorrection);

            $summaries[] = $currentSummary;
        }

        $earliestFirstSummaries = array_reverse($summaries);
        $json = $this->serializer->serialize($earliestFirstSummaries, 'json');

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    /**
     * @param $rootAlias
     *
     * @return DateTime[][]
     */
    private function getRecentStateChangesOfIssues(QueryBuilder $queryBuilder, $rootAlias, DateTime $backtrackDate): array
    {
        $queryBuilder->select($rootAlias.'.registeredAt registeredAt, '.$rootAlias.'.resolvedAt resolvedAt, '.$rootAlias.'.closedAt closedAt');
        $queryBuilder->orWhere($rootAlias.'.registeredAt IS NOT NULL')
            ->andWhere($rootAlias.'.registeredAt > :backtrack_1 OR '.$rootAlias.'.resolvedAt > :backtrack_2 OR '.$rootAlias.'.closedAt > :backtrack_3')
            ->setParameter(':backtrack_1', $backtrackDate)
            ->setParameter(':backtrack_2', $backtrackDate)
            ->setParameter(':backtrack_3', $backtrackDate);

        return $queryBuilder->getQuery()->getResult();
    }
}
