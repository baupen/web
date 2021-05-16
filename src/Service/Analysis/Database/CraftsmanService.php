<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Analysis\Database;

use App\Doctrine\UTCDateTimeType;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Service\Analysis\CraftsmanAnalysis;
use App\Service\Analysis\CraftsmanIssueAnalysis;
use App\Service\Report\Email\IssueCountTrait;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CraftsmanService
{
    private ManagerRegistry $manager;

    /**
     * IssueDatabaseService constructor.
     */
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Craftsman[]       $craftsmen
     * @param IssueCountTrait[] $issueCountByCraftsman
     */
    public function findIssueCountByCraftsman(array $craftsmen, array $issueCountByCraftsman)
    {
        $this->setOpenAndClosedCount($craftsmen, $issueCountByCraftsman);

        $queryBuilder = $this->getCraftsmanIssuesQueryBuilder('i', $craftsmen);
        $issueRepository = $this->manager->getRepository(Issue::class);
        $queryBuilderResolvedIssues = $issueRepository->filterResolvedIssues('i', clone $queryBuilder);
        $this->groupByCraftsmanAndEvaluate(
            $queryBuilderResolvedIssues, 'COUNT(i)',
            function (string $craftsmanId, $value) use ($issueCountByCraftsman) {
                $issueCountByCraftsman[$craftsmanId]->setResolvedCount($value);
            }
        );
    }

    /**
     * @param Craftsman[]              $craftsmen
     * @param CraftsmanIssueAnalysis[] $issueAnalysisByCraftsman
     */
    public function findIssueAnalysisByCraftsman(array $craftsmen, array $issueAnalysisByCraftsman)
    {
        $this->setOpenAndClosedCount($craftsmen, $issueAnalysisByCraftsman);

        $queryBuilder = $this->getCraftsmanIssuesQueryBuilder('i', $craftsmen);
        $issueRepository = $this->manager->getRepository(Issue::class);
        $queryBuilderInspectableIssues = $issueRepository->filterInspectableIssues('i', clone $queryBuilder);
        $this->groupByCraftsmanAndEvaluate(
            $queryBuilderInspectableIssues, 'COUNT(i)',
            function (string $craftsmanId, $value) use ($issueAnalysisByCraftsman) {
                $issueAnalysisByCraftsman[$craftsmanId]->setInspectableCount($value);
            }
        );

        $openIssuesQueryBuilder = $this->getOpenIssuesQueryBuilder('i', $craftsmen);
        $unreadIssuesQueryBuilder = (clone $openIssuesQueryBuilder)
            ->join('i.craftsman', 'c')
            ->andWhere('i.registeredAt > c.lastVisitOnline OR c.lastVisitOnline IS NULL');
        $this->groupByCraftsmanAndEvaluate(
            $unreadIssuesQueryBuilder, 'COUNT(i)',
            function (string $craftsmanId, $value) use ($issueAnalysisByCraftsman) {
                $issueAnalysisByCraftsman[$craftsmanId]->setUnreadCount($value);
            }
        );

        $overdueIssuesQueryBuilder = (clone $openIssuesQueryBuilder)
            ->andWhere('i.deadline IS NOT NULL')
            ->andWhere('i.deadline < :now')
            ->setParameter(':now', new \DateTime());
        $this->groupByCraftsmanAndEvaluate(
            $overdueIssuesQueryBuilder, 'COUNT(i)',
            function (string $craftsmanId, int $value) use ($issueAnalysisByCraftsman) {
                $issueAnalysisByCraftsman[$craftsmanId]->setOverdueCount($value);
            }
        );
    }

    private function setOpenAndClosedCount(array $craftsmen, $targetByCraftsman)
    {
        $openIssuesQueryBuilder = $this->getOpenIssuesQueryBuilder('i', $craftsmen);
        $issueRepository = $this->manager->getRepository(Issue::class);
        $this->groupByCraftsmanAndEvaluate(
            $openIssuesQueryBuilder, 'COUNT('.'i'.')',
            function (string $craftsmanId, $value) use ($targetByCraftsman) {
                $targetByCraftsman[$craftsmanId]->setOpenCount($value);
            }
        );

        $queryBuilder = $this->getCraftsmanIssuesQueryBuilder('i', $craftsmen);
        $queryBuilderClosedIssues = $issueRepository->filterClosedIssues('i', clone $queryBuilder);
        $this->groupByCraftsmanAndEvaluate(
            $queryBuilderClosedIssues, 'COUNT('.'i'.')',
            function (string $craftsmanId, $value) use ($targetByCraftsman) {
                $targetByCraftsman[$craftsmanId]->setClosedCount($value);
            }
        );
    }

    /**
     * @param Craftsman[]         $craftsmen
     * @param CraftsmanAnalysis[] $statisticsDictionary
     */
    public function findNextDeadline(array $craftsmen, array $statisticsDictionary)
    {
        $queryBuilder = $this->getOpenIssuesQueryBuilder('i', $craftsmen)
            ->andWhere('i.deadline IS NOT NULL');

        $this->groupByCraftsmanAndEvaluate(
            $queryBuilder, 'MIN(i.deadline)',
            function (string $craftsmanId, $value) use ($statisticsDictionary) {
                $statisticsDictionary[$craftsmanId]->setNextDeadline(UTCDateTimeType::tryParseDateTime($value));
            }
        );
    }

    /**
     * @param Craftsman[]         $craftsmen
     * @param CraftsmanAnalysis[] $statisticsDictionary
     */
    public function findLastIssueResolved(array $craftsmen, array $statisticsDictionary)
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

    public function getCraftsmanIssuesQueryBuilder(string $rootAlias, array $craftsmen)
    {
        $craftsmanIds = $this->getCraftsmanIds($craftsmen);

        $issueRepository = $this->manager->getRepository(Issue::class);

        return $issueRepository->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias.'.deletedAt IS NULL')
            ->andWhere($rootAlias.'.craftsman IN (:craftsmanIds)')
            ->setParameter(':craftsmanIds', $craftsmanIds);
    }

    private function getOpenIssuesQueryBuilder(string $rootAlias, array $craftsmen)
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
