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
use App\Entity\Filter;
use App\Entity\Issue;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class IssueRepository extends EntityRepository
{
    /**
     * @param ConstructionSite $constructionSite
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return int
     */
    public function getHighestNumber(ConstructionSite $constructionSite)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('i.number')->from(Issue::class, 'i');
        $qb->join('i.map', 'm');
        $qb->where('m.constructionSite = :constructionSite');
        $qb->setParameter(':constructionSite', $constructionSite);
        $qb->orderBy('i.number', 'DESC');
        $qb->setMaxResults(1);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Filter $filter
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return int
     */
    public function filterCount(Filter $filter)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(i.id)');

        //set conditions from filter
        $this->applyFilter($qb, $filter);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * gets recently changed issues.
     *
     * @param ConstructionSite $constructionSite
     * @param int $days
     *
     * @return Issue[]
     */
    public function getContextIssues(ConstructionSite $constructionSite, $days = 14)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('i');
        $queryBuilder->from(Issue::class, 'i');
        $queryBuilder->leftJoin('i.craftsman', 'c');
        $queryBuilder->where('c.constructionSite = :constructionSite');
        $queryBuilder->setParameter(':constructionSite', $constructionSite->getId());
        $queryBuilder->andWhere('i.lastChangedAt > :lastChangedAt');
        $queryBuilder->setParameter('lastChangedAt', new \DateTime('now -' . $days . ' days'));
        $queryBuilder->orderBy('i.lastChangedAt', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * apply the filter to the query builder.
     *
     * @param QueryBuilder $queryBuilder
     * @param Filter $filter
     */
    private function applyFilter(QueryBuilder $queryBuilder, Filter $filter)
    {
        // due to a bug in doctrine empty arrays are the same as null arrays after persist/retrieve from db
        // therefore handle empty arrays as null arrays in lack of a better solution
        // bugfix will only be included in 3.0 because its a breaking change
        $unsafeArrays = $filter->getId() !== null;

        $queryBuilder->from(Issue::class, 'i');
        $queryBuilder->leftJoin('i.craftsman', 'c');
        $queryBuilder->join('i.map', 'm');
        $queryBuilder->join('m.constructionSite', 'cs');
        $queryBuilder->where('cs.id = :constructionSite');
        $queryBuilder->setParameter(':constructionSite', $filter->getConstructionSite());
        $queryBuilder->orderBy('i.number', 'ASC');
        $queryBuilder->orderBy('i.createdAt', 'ASC');

        if ($filter->getCraftsmen() !== null && !($unsafeArrays && empty($filter->getCraftsmen()))) {
            $queryBuilder->andWhere('c.id IN (:craftsmen)');
            $queryBuilder->setParameter(':craftsmen', $filter->getCraftsmen());
        }

        if ($filter->getMaps() !== null && !($unsafeArrays && empty($filter->getMaps()))) {
            $queryBuilder->andWhere('m.id IN (:maps)');
            $queryBuilder->setParameter(':maps', $filter->getMaps());
        }

        if ($filter->getIssues() !== null && !($unsafeArrays && empty($filter->getIssues()))) {
            $queryBuilder->andWhere('i.id IN (:issues)');
            $queryBuilder->setParameter(':issues', $filter->getIssues());
        }

        $statusToString = function ($condition) {
            return 'IS ' . ($condition ? 'NOT ' : '') . 'NULL';
        };
        if ($filter->getRegistrationStatus() !== null) {
            $queryBuilder->andWhere('i.registeredAt ' . $statusToString($filter->getRegistrationStatus()));
            if ($filter->getRespondedStatus()) {
                if ($filter->getRegistrationStart() !== null) {
                    $queryBuilder->andWhere('i.registeredAt >= :registration_end');
                    $queryBuilder->setParameter(':responded_start', $filter->getRegistrationStart());
                }
                if ($filter->getRegistrationEnd() !== null) {
                    $queryBuilder->andWhere('i.registeredAt <= :registration_end');
                    $queryBuilder->setParameter(':registration_end', $filter->getRegistrationEnd());
                }
            }
        }

        if ($filter->getRespondedStatus() !== null) {
            $queryBuilder->andWhere('i.respondedAt ' . $statusToString($filter->getRespondedStatus()));
            if ($filter->getRespondedStatus()) {
                if ($filter->getRespondedStart() !== null) {
                    $queryBuilder->andWhere('i.respondedAt >= :responded_start');
                    $queryBuilder->setParameter(':responded_start', $filter->getRespondedStart());
                }
                if ($filter->getRespondedEnd() !== null) {
                    $queryBuilder->andWhere('i.respondedAt <= :responded_end');
                    $queryBuilder->setParameter(':responded_end', $filter->getRespondedEnd());
                }
            }
        }

        if ($filter->getReviewedStatus() !== null) {
            $queryBuilder->andWhere('i.reviewedAt ' . $statusToString($filter->getReviewedStatus()));
            if ($filter->getReviewedStatus()) {
                if ($filter->getReviewedStart() !== null) {
                    $queryBuilder->andWhere('i.reviewedAt >= :reviewed_start');
                    $queryBuilder->setParameter(':reviewed_start', $filter->getReviewedStart());
                }
                if ($filter->getReviewedEnd() !== null) {
                    $queryBuilder->andWhere('i.reviewedAt <= :reviewed_end');
                    $queryBuilder->setParameter(':reviewed_end', $filter->getReviewedEnd());
                }
            }
        }

        if ($filter->getReadStatus() !== null) {
            if ($filter->getReadStatus()) {
                $queryBuilder->andWhere('i.registeredAt < c.lastOnlineVisit');
            } else {
                $queryBuilder->andWhere('i.registeredAt >= c.lastOnlineVisit');
            }
        }
    }

    /**
     * @param Filter $filter
     *
     * @return Issue[]
     */
    public function filter(Filter $filter)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('i');

        //set conditions from filter
        $this->applyFilter($qb, $filter);

        /** @var Issue[] $issues */
        $issues = $qb->getQuery()->getResult();
        if ($filter->getNumberText() === null) {
            return $issues;
        }

        //filter by issue number text
        $res = [];
        foreach ($issues as $issue) {
            if (mb_strpos((string)$issue->getNumber(), $filter->getNumberText()) === 0) {
                $res[] = $issue;
            }
        }

        return $res;
    }
}
