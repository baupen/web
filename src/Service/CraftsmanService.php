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

use App\Doctrine\UTCDateTimeType;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Service\Craftsman\Statistics;
use App\Service\Interfaces\CraftsmanServiceInterface;
use App\Service\Interfaces\IssueServiceInterface;
use App\Service\Issue\Summary;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CraftsmanService implements CraftsmanServiceInterface
{
    /**
     * @var ManagerRegistry
     */
    private $manager;

    /**
     * @var IssueServiceInterface
     */
    private $issueService;

    /**
     * CraftsmanService constructor.
     *
     * @param IssueServiceInterface $issueService
     */
    public function __construct(ManagerRegistry $manager, Interfaces\IssueServiceInterface $issueService)
    {
        $this->manager = $manager;
        $this->issueService = $issueService;
    }

    /**
     * @param Craftsman[] $craftsmen
     *
     * @return Statistics[]
     */
    public function createStatisticLookup(array $craftsmen): array
    {
        /** @var Summary[] $craftsmanIssueSummaryLookup */
        $craftsmanIssueSummaryLookup = $this->createIssueSummaryLookup($craftsmen);

        $statisticsDictionary = [];
        foreach ($craftsmanIssueSummaryLookup as $craftsmanId => $issueSummary) {
            $statisticsDictionary[$craftsmanId] = Statistics::createWithSummary($issueSummary);
        }

        $this->countUnreadIssues($craftsmen, $statisticsDictionary);
        $this->countOverdueIssues($craftsmen, $statisticsDictionary);

        $this->findNextDeadline($craftsmen, $statisticsDictionary);
        $this->findLastIssueResolved($craftsmen, $statisticsDictionary);
        $this->findLastActivity($craftsmen, $statisticsDictionary);

        return $statisticsDictionary;
    }

    public function getCurrentAndPastSummaryLookup(array $craftsmen, \DateTime $pastDate): array
    {
        $issueSummaries = $this->createIssueSummaryLookup($craftsmen);

        $issueRepository = $this->manager->getRepository(Issue::class);
        $rootAlias = 'i';
        $queryBuilder = $this->getCraftsmanIssuesQueryBuilder($rootAlias, $craftsmen)->addSelect('identity('.$rootAlias.'.craftsman) AS craftsman');
        $stateChangeIssues = $issueRepository->getStateChangeIssues($queryBuilder, $rootAlias, $pastDate);

        $stateChangeIssuesByCraftsman = [];
        foreach ($this->getCraftsmanIds($craftsmen) as $craftsmanId) {
            $stateChangeIssuesByCraftsman[$craftsmanId] = [];
        }

        foreach ($stateChangeIssues as $stateChangeIssue) {
            $craftsman = $stateChangeIssue['craftsman'];
            $stateChangeIssuesByCraftsman[$craftsman][] = $stateChangeIssue;
        }

        $craftsmanSummaries = [];
        foreach ($issueSummaries as $craftsmanId => $issueSummary) {
            $stateChangeIssues = $stateChangeIssuesByCraftsman[$craftsmanId];
            list($deltaSummary) = $this->issueService->createDeltaSummaries($issueSummary, $stateChangeIssues, $pastDate);
            $craftsmanSummaries[$craftsmanId] = [$issueSummary, $deltaSummary];
        }

        return $craftsmanSummaries;
    }

    private function createIssueSummaryLookup(array $craftsmen): array
    {
        $summaryLookup = [];
        foreach ($craftsmen as $craftsman) {
            $summaryLookup[$craftsman->getId()] = new Summary();
        }

        $issueRepository = $this->manager->getRepository(Issue::class);

        $rootAlias = 'i';
        $countSelectExpression = 'COUNT('.$rootAlias.')';
        $queryBuilder = $this->getCraftsmanIssuesQueryBuilder($rootAlias, $craftsmen);

        $queryBuilderOpenIssues = $issueRepository->filterOpenIssues($rootAlias, clone $queryBuilder);
        $this->groupByCraftsmanAndEvaluate(
            $queryBuilderOpenIssues, $countSelectExpression,
            function (string $craftsmanId, $value) use ($summaryLookup) {
                $summaryLookup[$craftsmanId]->setOpenCount($value);
            }
        );

        $queryBuilderResolvedIssues = $issueRepository->filterInspectableIssues($rootAlias, clone $queryBuilder);
        $this->groupByCraftsmanAndEvaluate(
            $queryBuilderResolvedIssues, $countSelectExpression,
            function (string $craftsmanId, $value) use ($summaryLookup) {
                $summaryLookup[$craftsmanId]->setInspectableCount($value);
            }
        );

        $queryBuilderClosedIssues = $issueRepository->filterClosedIssues($rootAlias, clone $queryBuilder);
        $this->groupByCraftsmanAndEvaluate(
            $queryBuilderClosedIssues, $countSelectExpression,
            function (string $craftsmanId, $value) use ($summaryLookup) {
                $summaryLookup[$craftsmanId]->setClosedCount($value);
            }
        );

        return $summaryLookup;
    }

    /**
     * @param Craftsman[]  $craftsmen
     * @param Statistics[] $statisticsDictionary
     */
    private function countUnreadIssues(array $craftsmen, array $statisticsDictionary)
    {
        $queryBuilder = $this->getOpenCraftsmanIssuesQueryBuilder('i', $craftsmen)
            ->join('i.craftsman', 'c')
            ->andWhere('i.registeredAt > c.lastVisitOnline OR c.lastVisitOnline IS NULL');

        $this->groupByCraftsmanAndEvaluate(
            $queryBuilder, 'COUNT(i)',
            function (string $craftsmanId, $value) use ($statisticsDictionary) {
                $statisticsDictionary[$craftsmanId]->setIssueUnreadCount($value);
            }
        );
    }

    /**
     * @param Craftsman[]  $craftsmen
     * @param Statistics[] $statisticsDictionary
     */
    private function countOverdueIssues(array $craftsmen, array $statisticsDictionary)
    {
        $queryBuilder = $this->getOpenCraftsmanIssuesQueryBuilder('i', $craftsmen)
            ->andWhere('i.deadline IS NOT NULL')
            ->andWhere('i.deadline < :now')
            ->setParameter(':now', new \DateTime());

        $this->groupByCraftsmanAndEvaluate(
            $queryBuilder, 'COUNT(i)',
            function (string $craftsmanId, int $value) use ($statisticsDictionary) {
                $statisticsDictionary[$craftsmanId]->setIssueOverdueCount($value);
            }
        );
    }

    /**
     * @param Craftsman[]  $craftsmen
     * @param Statistics[] $statisticsDictionary
     */
    private function findNextDeadline(array $craftsmen, array $statisticsDictionary)
    {
        $queryBuilder = $this->getOpenCraftsmanIssuesQueryBuilder('i', $craftsmen)
            ->andWhere('i.deadline IS NOT NULL');

        $this->groupByCraftsmanAndEvaluate(
            $queryBuilder, 'MIN(i.deadline)',
            function (string $craftsmanId, $value) use ($statisticsDictionary) {
                $statisticsDictionary[$craftsmanId]->setNextDeadline(UTCDateTimeType::tryParseDateTime($value));
            }
        );
    }

    /**
     * @param Craftsman[]  $craftsmen
     * @param Statistics[] $statisticsDictionary
     */
    private function findLastIssueResolved(array $craftsmen, array $statisticsDictionary)
    {
        $rootAlias = 'i';
        $queryBuilder = $this->getCraftsmanIssuesQueryBuilder($rootAlias, $craftsmen)
            ->andWhere($rootAlias.'.registeredAt IS NOT NULL');

        $this->groupByCraftsmanAndEvaluate(
            $queryBuilder, 'MAX(i.resolvedAt)',
            function (string $craftsmanId, $value) use ($statisticsDictionary) {
                $statisticsDictionary[$craftsmanId]->setLastIssueResolved(UTCDateTimeType::tryParseDateTime($value));
            }
        );
    }

    private function getCraftsmanIssuesQueryBuilder(string $rootAlias, array $craftsmen)
    {
        $craftsmanIds = $this->getCraftsmanIds($craftsmen);

        $issueRepository = $this->manager->getRepository(Issue::class);

        return $issueRepository->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias.'.deletedAt IS NULL')
            ->andWhere($rootAlias.'.craftsman IN (:craftsmanIds)')
            ->setParameter(':craftsmanIds', $craftsmanIds);
    }

    private function getOpenCraftsmanIssuesQueryBuilder(string $rootAlias, array $craftsmen)
    {
        $queryBuilder = $this->getCraftsmanIssuesQueryBuilder($rootAlias, $craftsmen);

        $issueRepository = $this->manager->getRepository(Issue::class);
        $issueRepository->filterOpenIssues($rootAlias, $queryBuilder);

        return $queryBuilder;
    }

    private function groupByCraftsmanAndEvaluate(QueryBuilder $queryBuilder, string $selectExpression, \Closure $processResult)
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->groupBy($rootAlias.'.craftsman')
            ->select('identity('.$rootAlias.'.craftsman)')
            ->addSelect($selectExpression);

        $nextDeadlineResult = $queryBuilder->getQuery()->getResult();

        foreach ($nextDeadlineResult as $entry) {
            list($craftsmanId, $value) = array_values($entry);
            $processResult($craftsmanId, $value);
        }
    }

    /**
     * @param Craftsman[]  $craftsmen
     * @param Statistics[] $statisticsDictionary
     */
    private function findLastActivity(array $craftsmen, array $statisticsDictionary)
    {
        foreach ($craftsmen as $craftsman) {
            $statisticsDictionary[$craftsman->getId()]->setLastEmailReceived($craftsman->getLastEmailReceived());
            $statisticsDictionary[$craftsman->getId()]->setLastVisitOnline($craftsman->getLastVisitOnline());
        }
    }

    /**
     * @param Craftsman[] $craftsmen
     *
     * @return string[]
     */
    private function getCraftsmanIds(array $craftsmen): array
    {
        $craftsmanIds = [];
        foreach ($craftsmen as $craftsman) {
            $craftsmanIds[] = $craftsman->getId();
        }

        return $craftsmanIds;
    }
}
