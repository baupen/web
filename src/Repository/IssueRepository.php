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

        return $qb->getQuery()->getResult();
    }
}
