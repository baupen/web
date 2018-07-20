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
     * @return Issue[]
     */
    public function filter(Filter $filter)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('i')->from(Issue::class, 'i');
        $qb->join('i.map', 'm');
        $qb->join('i.craftsman', 'c');
        $qb->join('m.constructionSite', 'cs');
        $qb->where('cs.id = :constructionSite');
        $qb->setParameter(':constructionSite', $filter->getConstructionSite());
        $qb->orderBy('i.number', 'ASC');
        $qb->orderBy('i.createdAt', 'ASC');

        if ($filter->getCraftsmen() !== null) {
            $qb->andWhere('c.id IN (:craftsmen)');
            $qb->setParameter(':craftsmen', $filter->getCraftsmen());
        }

        if ($filter->getMaps() !== null) {
            $qb->andWhere('m.id IN (:maps)');
            $qb->setParameter(':maps', $filter->getMaps());
        }

        $statusToString = function ($condition) {
            return 'IS ' . ($condition ? 'NOT ' : '') . 'NULL';
        };
        if ($filter->getRegistrationStatus() !== null) {
            $qb->andWhere('i.registeredAt ' . $statusToString($filter->getRegistrationStatus()));
            if ($filter->getRespondedStatus()) {
                if ($filter->getRegistrationStart() !== null) {
                    $qb->andWhere('i.registeredAt >= :registration_end');
                    $qb->setParameter(':responded_start', $filter->getRegistrationStart());
                }
                if ($filter->getRegistrationEnd() !== null) {
                    $qb->andWhere('i.registeredAt <= :registration_end');
                    $qb->setParameter(':registration_end', $filter->getRegistrationEnd());
                }
            }
        }

        if ($filter->getRespondedStatus() !== null) {
            $qb->andWhere('i.respondedAt ' . $statusToString($filter->getRespondedStatus()));
            if ($filter->getRespondedStatus()) {
                if ($filter->getRespondedStart() !== null) {
                    $qb->andWhere('i.respondedAt >= :responded_start');
                    $qb->setParameter(':responded_start', $filter->getRespondedStart());
                }
                if ($filter->getRespondedEnd() !== null) {
                    $qb->andWhere('i.respondedAt <= :responded_end');
                    $qb->setParameter(':responded_end', $filter->getRespondedEnd());
                }
            }
        }

        if ($filter->getReviewedStatus() !== null) {
            $qb->andWhere('i.reviewedAt ' . $statusToString($filter->getReviewedStatus()));
            if ($filter->getReviewedStatus()) {
                if ($filter->getReviewedStart() !== null) {
                    $qb->andWhere('i.reviewedAt >= :reviewed_start');
                    $qb->setParameter(':reviewed_start', $filter->getReviewedStart());
                }
                if ($filter->getReviewedEnd() !== null) {
                    $qb->andWhere('i.reviewedAt <= :reviewed_end');
                    $qb->setParameter(':reviewed_end', $filter->getReviewedEnd());
                }
            }
        }

        if ($filter->getReadStatus() !== null) {
            if ($filter->getReadStatus()) {
                $qb->andWhere('i.registeredAt < c.lastOnlineVisit');
            } else {
                $qb->andWhere('i.registeredAt >= c.lastOnlineVisit');
            }
        }

        return $qb->getQuery()->getResult();
    }
}
