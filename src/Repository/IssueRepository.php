<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\ConstructionSite;
use App\Entity\Issue;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class IssueRepository extends EntityRepository
{
    public function setHighestNumber(Issue $issue)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('i.number')->from(Issue::class, 'i');
        $qb->where('i.constructionSite = :constructionSite');
        $qb->setParameter(':constructionSite', $issue->getConstructionSite());
        $qb->orderBy('i.number', 'DESC');
        $qb->setMaxResults(1);

        $issue->setNumber($qb->getQuery()->getSingleScalarResult() + 1);
    }

    public function findByConstructionSite(array $issueIds, ConstructionSite $constructionSite)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('i')
            ->from(Issue::class, 'i')
            ->where('i.id IN (:ids)')
            ->setParameter(':ids', $issueIds)
            ->andWhere('i.constructionSite = :constructionSite')
            ->setParameter(':constructionSite', $constructionSite->getId());

        return $qb->getQuery()->getResult();
    }

    /**
     * @return int[]
     */
    public function countOpenResolvedAndClosed(string $rootAlias, QueryBuilder $queryBuilder): array
    {
        $openCount = $this->filterAndCount($rootAlias, $queryBuilder, [$this, 'filterOpenIssues']);
        $resolvedCount = $this->filterAndCount($rootAlias, $queryBuilder, [$this, 'filterResolvedIssues']);
        $closedCount = $this->filterAndCount($rootAlias, $queryBuilder, [$this, 'filterClosedIssues']);

        return [$openCount, $resolvedCount, $closedCount];
    }

    private function filterAndCount(string $rootAlias, QueryBuilder $builder, callable $filter): int
    {
        $filteredBuilder = $filter($rootAlias, clone $builder);

        return $this->countResult($rootAlias, $filteredBuilder);
    }

    public function filterOpenIssues(string $rootAlias, QueryBuilder $builder): QueryBuilder
    {
        $builder->andWhere($rootAlias.'.registeredAt IS NOT NULL')
            ->andWhere($rootAlias.'.resolvedAt IS NULL')
            ->andWhere($rootAlias.'.closedAt IS NULL');

        return $builder;
    }

    public function filterResolvedIssues(string $rootAlias, QueryBuilder $builder): QueryBuilder
    {
        $builder->andWhere($rootAlias.'.registeredAt IS NOT NULL')
            ->andWhere($rootAlias.'.resolvedAt IS NOT NULL')
            ->andWhere($rootAlias.'.closedAt IS NULL');

        return $builder;
    }

    public function filterClosedIssues(string $rootAlias, QueryBuilder $builder): QueryBuilder
    {
        $builder->andWhere($rootAlias.'.registeredAt IS NOT NULL')
            ->andWhere($rootAlias.'.closedAt IS NOT NULL');

        return $builder;
    }

    private function countResult(string $rootAlias, QueryBuilder $builder): int
    {
        return $builder->select('count('.$rootAlias.')')
            ->getQuery()->getSingleScalarResult();
    }
}
