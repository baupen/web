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
use function count;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Exception;

class IssueRepository extends EntityRepository
{
    /**
     * @param ConstructionSite $constructionSite
     *
     * @throws NonUniqueResultException
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
     * @return int
     */
    public function countByFilter(Filter $filter)
    {
        return \count($this->findByFilter($filter));
    }

    /**
     * gets recently changed issues.
     *
     * @param ConstructionSite $constructionSite
     * @param int              $days
     *
     * @throws Exception
     *
     * @return Issue[]
     */
    public function findByRecentlyChanged(ConstructionSite $constructionSite, $days = 14)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('i');
        $queryBuilder->from(Issue::class, 'i');
        $queryBuilder->leftJoin('i.craftsman', 'c');
        $queryBuilder->where('c.constructionSite = :constructionSite');
        $queryBuilder->setParameter(':constructionSite', $constructionSite->getId());
        $queryBuilder->andWhere('i.lastChangedAt > :lastChangedAt');
        $queryBuilder->setParameter('lastChangedAt', new DateTime('now -' . $days . ' days'));
        $queryBuilder->orderBy('i.lastChangedAt', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Filter $filter
     *
     * @return Issue[]
     */
    public function findByFilter(Filter $filter)
    {
        //set conditions from filter
        $queryBuilder = $this->createQueryBuilderFromFilter($filter);

        /** @var Issue[] $issues */
        $issues = $queryBuilder->getQuery()->getResult();

        return $this->applyFilterToIssues($filter, $issues);
    }

    /**
     * apply the filter to the query builder.
     *
     * @param Filter $filter
     *
     * @return QueryBuilder
     */
    private function createQueryBuilderFromFilter(Filter $filter)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('i')
            ->from(Issue::class, 'i')
            ->leftJoin('i.craftsman', 'c')
            ->join('i.map', 'm')
            ->join('m.constructionSite', 'cs')
            ->where('cs.id = :constructionSite')
            ->setParameter(':constructionSite', $filter->getConstructionSite())
            ->orderBy('i.number', 'ASC')
            ->orderBy('i.createdAt', 'ASC')
        ;

        if ($filter->getCraftsmen() !== null) {
            $queryBuilder->andWhere('c.id IN (:craftsmen)')
                ->setParameter(':craftsmen', $filter->getCraftsmen())
            ;
        }
        if ($filter->getTrades() !== null) {
            $queryBuilder->andWhere('c.trade IN (:trades)')
                ->setParameter(':trades', $filter->getTrades())
            ;
        }

        if ($filter->getMaps() !== null) {
            $queryBuilder->andWhere('m.id IN (:maps)')
                ->setParameter(':maps', $filter->getMaps())
            ;
        }

        if ($filter->getIssues() !== null) {
            $queryBuilder->andWhere('i.id IN (:issues)')
                ->setParameter(':issues', $filter->getIssues())
            ;
        }

        $statusToString = function ($condition) {
            return 'IS ' . ($condition ? 'NOT ' : '') . 'NULL';
        };
        if ($filter->getRegistrationStatus() !== null) {
            $queryBuilder->andWhere('i.registeredAt ' . $statusToString($filter->getRegistrationStatus()));
            if ($filter->getRespondedStatus()) {
                if ($filter->getRegistrationStart() !== null) {
                    $queryBuilder->andWhere('i.registeredAt >= :registration_end')
                        ->setParameter(':responded_start', $filter->getRegistrationStart())
                    ;
                }
                if ($filter->getRegistrationEnd() !== null) {
                    $queryBuilder->andWhere('i.registeredAt <= :registration_end')
                        ->setParameter(':registration_end', $filter->getRegistrationEnd())
                    ;
                }
            }
        }

        if ($filter->getRespondedStatus() !== null) {
            $queryBuilder->andWhere('i.respondedAt ' . $statusToString($filter->getRespondedStatus()));
            if ($filter->getRespondedStatus()) {
                if ($filter->getRespondedStart() !== null) {
                    $queryBuilder->andWhere('i.respondedAt >= :responded_start')
                        ->setParameter(':responded_start', $filter->getRespondedStart())
                    ;
                }
                if ($filter->getRespondedEnd() !== null) {
                    $queryBuilder->andWhere('i.respondedAt <= :responded_end')
                        ->setParameter(':responded_end', $filter->getRespondedEnd())
                    ;
                }
            }
        }

        if ($filter->getReviewedStatus() !== null) {
            $queryBuilder->andWhere('i.reviewedAt ' . $statusToString($filter->getReviewedStatus()));
            if ($filter->getReviewedStatus()) {
                if ($filter->getReviewedStart() !== null) {
                    $queryBuilder->andWhere('i.reviewedAt >= :reviewed_start')
                        ->setParameter(':reviewed_start', $filter->getReviewedStart())
                    ;
                }
                if ($filter->getReviewedEnd() !== null) {
                    $queryBuilder->andWhere('i.reviewedAt <= :reviewed_end')
                        ->setParameter(':reviewed_end', $filter->getReviewedEnd())
                    ;
                }
            }
        }

        if ($filter->getLimitStart() !== null) {
            $queryBuilder->andWhere('i.responseLimit >= :response_limit_start')
                ->setParameter(':response_limit_start', $filter->getLimitStart())
            ;
        }

        if ($filter->getLimitEnd() !== null) {
            $queryBuilder->andWhere('i.responseLimit <= :response_limit_end')
                ->setParameter(':response_limit_end', $filter->getLimitEnd())
            ;
        }

        if ($filter->getIsMarked() !== null) {
            $queryBuilder->andWhere('i.isMarked = :is_marked')
                ->setParameter('is_marked', $filter->getIsMarked())
            ;
        }

        return $queryBuilder;
    }

    /**
     * @param Filter  $filter
     * @param Issue[] $issues
     *
     * @return Issue[]|array
     */
    private function applyFilterToIssues(Filter $filter, array $issues)
    {
        if ($filter->getAnyStatus() !== null) {
            // count matches of status per issue
            $matches = [];
            foreach ($issues as $issue) {
                $issueId = $issue->getId();

                $matches[$issueId] = 0;
                if ($filter->getAnyStatus() & Filter::STATUS_REGISTERED) {
                    $matches[$issueId] += $issue->getRegisteredAt() !== null && $issue->getCraftsman()->getLastOnlineVisit() < $issue->getRegisteredAt() && $issue->getRespondedAt() === null && $issue->getReviewedAt() === null;
                }
                if ($filter->getAnyStatus() & Filter::STATUS_READ) {
                    $matches[$issueId] += $issue->getCraftsman()->getLastOnlineVisit() !== null && $issue->getCraftsman()->getLastOnlineVisit() > $issue->getRegisteredAt() && $issue->getRespondedAt() === null && $issue->getReviewedAt() === null;
                }
                if ($filter->getAnyStatus() & Filter::STATUS_RESPONDED) {
                    $matches[$issueId] += $issue->getRespondedAt() !== null && $issue->getReviewedAt() === null;
                }
                if ($filter->getAnyStatus() & Filter::STATUS_REVIEWED) {
                    $matches[$issueId] += $issue->getReviewedAt() !== null;
                }
            }

            // only keep issues with at least one match
            $res = [];
            foreach ($issues as $issue) {
                if ($matches[$issue->getId()] > 0) {
                    $res[] = $issue;
                }
            }

            $issues = $res;
        }

        return $issues;
    }
}
