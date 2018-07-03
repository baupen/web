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
        }

        //more properties missing

        return $qb->getQuery()->getResult();
    }
}
