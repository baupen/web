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

use App\Entity\Issue;
use App\Repository\IssueRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class IssueService
{
    private ManagerRegistry $manager;

    /**
     * @var IssueRepository
     */
    private $issueRepository;

    /**
     * IssueDatabaseService constructor.
     */
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
        $this->issueRepository = $this->manager->getRepository(Issue::class);
    }

    public function getStateChangeIssues(QueryBuilder $queryBuilder, string $rootAlias, \DateTime $backtrackDate): array
    {
        $queryBuilder->addSelect($rootAlias.'.registeredAt registeredAt, '.$rootAlias.'.resolvedAt resolvedAt, '.$rootAlias.'.closedAt closedAt');
        $queryBuilder
            ->andWhere($rootAlias.'.registeredAt > :backtrack_1 OR '.$rootAlias.'.resolvedAt > :backtrack_2 OR '.$rootAlias.'.closedAt > :backtrack_3')
            ->setParameter(':backtrack_1', $backtrackDate)
            ->setParameter(':backtrack_2', $backtrackDate)
            ->setParameter(':backtrack_3', $backtrackDate);

        return $queryBuilder->getQuery()->getResult();
    }

    public function countNewIssues(string $rootAlias, QueryBuilder $queryBuilder): int
    {
        return $this->filterAndCount($rootAlias, $queryBuilder, [$this->issueRepository, 'filterNewIssues']);
    }

    public function countOpenIssues(string $rootAlias, QueryBuilder $queryBuilder): int
    {
        return $this->filterAndCount($rootAlias, $queryBuilder, [$this->issueRepository, 'filterOpenIssues']);
    }

    public function countInspectableIssues(string $rootAlias, QueryBuilder $queryBuilder): int
    {
        return $this->filterAndCount($rootAlias, $queryBuilder, [$this->issueRepository, 'filterInspectableIssues']);
    }

    public function countClosedIssues(string $rootAlias, QueryBuilder $queryBuilder): int
    {
        return $this->filterAndCount($rootAlias, $queryBuilder, [$this->issueRepository, 'filterClosedIssues']);
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
