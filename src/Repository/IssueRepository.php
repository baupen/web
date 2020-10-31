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
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Exception;

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

    /**
     * @return int
     */
    public function countByFilter(Filter $filter)
    {
        return \count($this->findByFilter($filter));
    }

    /**
     * gets recently changed issues.
     *
     * @param int $days
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
        $queryBuilder->setParameter('lastChangedAt', new DateTime('now -'.$days.' days'));
        $queryBuilder->orderBy('i.lastChangedAt', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return Issue[]
     */
    public function findByFilter(Filter $filter)
    {
        $queryBuilder = $this->createQueryBuilderFromFilter($filter);

        /** @var Issue[] $issues */
        $issues = $queryBuilder->getQuery()->getResult();

        return $this->applyFilterToIssues($filter, $issues);
    }

    /**
     * apply the filter to the query builder.
     *
     * @return QueryBuilder
     */
    private function createQueryBuilderFromFilter(Filter $filter)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('i')
            ->addSelect('p')
            ->addSelect('m')
            ->addSelect('c')
            ->from(Issue::class, 'i')
            ->leftJoin('i.craftsman', 'c')
            ->leftJoin('i.position', 'p')
            ->join('i.map', 'm')
            ->join('m.constructionSite', 'cs')
            ->where('cs.id = :constructionSite')
            ->setParameter(':constructionSite', $filter->getConstructionSite())
            ->orderBy('i.number', 'ASC')
            ->orderBy('i.createdAt', 'ASC')
        ;

        if (null !== $filter->getCraftsmen()) {
            $queryBuilder->andWhere('c.id IN (:craftsmen)')
                ->setParameter(':craftsmen', $filter->getCraftsmen())
            ;
        }
        if (null !== $filter->getTrades()) {
            $queryBuilder->andWhere('c.trade IN (:trades)')
                ->setParameter(':trades', $filter->getTrades())
            ;
        }

        if (null !== $filter->getMaps()) {
            $queryBuilder->andWhere('m.id IN (:maps)')
                ->setParameter(':maps', $filter->getMaps())
            ;
        }

        if (null !== $filter->getIssues()) {
            $queryBuilder->andWhere('i.id IN (:issues)')
                ->setParameter(':issues', $filter->getIssues())
            ;
        }

        $statusToString = function ($condition) {
            return 'IS '.($condition ? 'NOT ' : '').'NULL';
        };
        if (null !== $filter->getRegistrationStatus()) {
            $queryBuilder->andWhere('i.registeredAt '.$statusToString($filter->getRegistrationStatus()));
            if ($filter->getRegistrationStatus()) {
                if (null !== $filter->getRegistrationStart()) {
                    $queryBuilder->andWhere('i.registeredAt >= :registration_start')
                        ->setParameter(':registration_start', $filter->getRegistrationStart())
                    ;
                }
                if (null !== $filter->getRegistrationEnd()) {
                    $queryBuilder->andWhere('i.registeredAt <= :registration_end')
                        ->setParameter(':registration_end', $filter->getRegistrationEnd())
                    ;
                }
            }
        }

        if (null !== $filter->getRespondedStatus()) {
            $queryBuilder->andWhere('i.respondedAt '.$statusToString($filter->getRespondedStatus()));
            if ($filter->getRespondedStatus()) {
                if (null !== $filter->getRespondedStart()) {
                    $queryBuilder->andWhere('i.respondedAt >= :responded_start')
                        ->setParameter(':responded_start', $filter->getRespondedStart())
                    ;
                }
                if (null !== $filter->getRespondedEnd()) {
                    $queryBuilder->andWhere('i.respondedAt <= :responded_end')
                        ->setParameter(':responded_end', $filter->getRespondedEnd())
                    ;
                }
            }
        }

        if (null !== $filter->getReviewedStatus()) {
            $queryBuilder->andWhere('i.reviewedAt '.$statusToString($filter->getReviewedStatus()));
            if ($filter->getReviewedStatus()) {
                if (null !== $filter->getReviewedStart()) {
                    $queryBuilder->andWhere('i.reviewedAt >= :reviewed_start')
                        ->setParameter(':reviewed_start', $filter->getReviewedStart())
                    ;
                }
                if (null !== $filter->getReviewedEnd()) {
                    $queryBuilder->andWhere('i.reviewedAt <= :reviewed_end')
                        ->setParameter(':reviewed_end', $filter->getReviewedEnd())
                    ;
                }
            }
        }

        if (null !== $filter->getLimitStart()) {
            $queryBuilder->andWhere('i.responseLimit >= :response_limit_start')
                ->setParameter(':response_limit_start', $filter->getLimitStart())
            ;
        }

        if (null !== $filter->getLimitEnd()) {
            $queryBuilder->andWhere('i.responseLimit <= :response_limit_end')
                ->setParameter(':response_limit_end', $filter->getLimitEnd())
            ;
        }

        if (null !== $filter->getIsMarked()) {
            $queryBuilder->andWhere('i.isMarked = :is_marked')
                ->setParameter('is_marked', $filter->getIsMarked())
            ;
        }

        if (null !== $filter->getWasAddedWithClient()) {
            $queryBuilder->andWhere('i.wasAddedWithClient = :was_added_with_client')
                ->setParameter('was_added_with_client', $filter->getWasAddedWithClient())
            ;
        }

        return $queryBuilder;
    }

    /**
     * @param Issue[] $issues
     *
     * @return Issue[]|array
     */
    private function applyFilterToIssues(Filter $filter, array $issues)
    {
        if (null !== $filter->getAnyStatus()) {
            // count matches of status per issue
            $matches = [];
            foreach ($issues as $issue) {
                $issueId = $issue->getId();

                $matches[$issueId] = 0;
                if ($filter->getAnyStatus() & Filter::STATUS_NEW) {
                    $matches[$issueId] += null === $issue->getRegisteredAt();
                }
                if ($filter->getAnyStatus() & Filter::STATUS_REGISTERED) {
                    $matches[$issueId] += null !== $issue->getRegisteredAt() && $issue->getCraftsman()->getLastOnlineVisit() < $issue->getRegisteredAt() && null === $issue->getRespondedAt() && null === $issue->getReviewedAt();
                }
                if ($filter->getAnyStatus() & Filter::STATUS_READ) {
                    $matches[$issueId] += null !== $issue->getCraftsman()->getLastOnlineVisit() && $issue->getCraftsman()->getLastOnlineVisit() > $issue->getRegisteredAt() && null === $issue->getRespondedAt() && null === $issue->getReviewedAt();
                }
                if ($filter->getAnyStatus() & Filter::STATUS_RESPONDED) {
                    $matches[$issueId] += null !== $issue->getRespondedAt() && null === $issue->getReviewedAt();
                }
                if ($filter->getAnyStatus() & Filter::STATUS_REVIEWED) {
                    $matches[$issueId] += null !== $issue->getReviewedAt();
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
