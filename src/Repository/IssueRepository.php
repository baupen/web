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
use DateTime;
use Doctrine\ORM\EntityRepository;
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
}
