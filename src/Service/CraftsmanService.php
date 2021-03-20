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
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CraftsmanService implements CraftsmanServiceInterface
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

    /**
     * @param Craftsman[] $craftsmen
     *
     * @return Statistics[]
     */
    public function createStatisticLookup(array $craftsmen): array
    {
        $statisticsDictionary = [];
        foreach ($craftsmen as $craftsman) {
            $statisticsDictionary[$craftsman->getId()] = new Statistics();
        }

        $this->createIssueSummaryLookup($craftsmen, $statisticsDictionary);

        $this->countUnreadIssues($craftsmen, $statisticsDictionary);
        $this->countOverdueIssues($craftsmen, $statisticsDictionary);

        $this->findNextDeadline($craftsmen, $statisticsDictionary);
        $this->findLastIssueResolved($craftsmen, $statisticsDictionary);
        $this->findLastActivity($craftsmen, $statisticsDictionary);

        return $statisticsDictionary;
    }

    private function createIssueSummaryLookup(array $craftsmen, array $statisticsDictionary)
    {
        $issueRepository = $this->manager->getRepository(Issue::class);

        $rootAlias = 'i';
        $queryBuilder = $this->getCraftsmanIssuesQueryBuilder($rootAlias, $craftsmen);

        $queryBuilderOpenIssues = $issueRepository->filterOpenIssues($rootAlias, clone $queryBuilder);
        $this->groupByCraftsmanAndEvaluate(
            $queryBuilderOpenIssues, $statisticsDictionary, 'COUNT(i)',
            function (Statistics $statistics, $value) {
                $statistics->getIssueSummary()->setOpenCount($value);
            }
        );

        $queryBuilderResolvedIssues = $issueRepository->filterInspectableIssues($rootAlias, clone $queryBuilder);
        $this->groupByCraftsmanAndEvaluate(
            $queryBuilderResolvedIssues, $statisticsDictionary, 'COUNT(i)',
            function (Statistics $statistics, $value) {
                $statistics->getIssueSummary()->setInspectableCount($value);
            }
        );

        $queryBuilderClosedIssues = $issueRepository->filterClosedIssues($rootAlias, clone $queryBuilder);
        $this->groupByCraftsmanAndEvaluate(
            $queryBuilderClosedIssues, $statisticsDictionary, 'COUNT(i)',
            function (Statistics $statistics, $value) {
                $statistics->getIssueSummary()->setClosedCount($value);
            }
        );
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
            $queryBuilder, $statisticsDictionary, 'COUNT(i)',
            function (Statistics $statistics, $value) {
                $statistics->setIssueUnreadCount($value);
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
            $queryBuilder, $statisticsDictionary, 'COUNT(i)',
            function (Statistics $statistics, $value) {
                $statistics->setIssueOverdueCount($value);
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
            $queryBuilder, $statisticsDictionary, 'MIN(i.deadline)',
            function (Statistics $statistics, $value) {
                $statistics->setNextDeadline(UTCDateTimeType::tryParseDateTime($value));
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
            $queryBuilder, $statisticsDictionary, 'MAX(i.resolvedAt)',
            function (Statistics $statistics, $value) {
                $statistics->setLastIssueResolved(UTCDateTimeType::tryParseDateTime($value));
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

    private function groupByCraftsmanAndEvaluate(QueryBuilder $queryBuilder, array $statisticsDictionary, string $selectExpression, \Closure $processResult)
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->groupBy($rootAlias.'.craftsman')
            ->select('identity('.$rootAlias.'.craftsman)')
            ->addSelect($selectExpression);

        $nextDeadlineResult = $queryBuilder->getQuery()->getResult();

        foreach ($nextDeadlineResult as $entry) {
            list($craftsmanId, $value) = array_values($entry);
            $processResult($statisticsDictionary[$craftsmanId], $value);
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
