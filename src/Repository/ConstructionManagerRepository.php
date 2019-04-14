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
use App\Security\Model\UserToken;
use Doctrine\ORM\EntityRepository;

class ConstructionManagerRepository extends EntityRepository
{
    /**
     * @param UserToken $token
     *
     * @return object|null
     */
    public function fromUserToken(UserToken $token)
    {
        return $this->findOneBy(['email' => $token->getUsername()]);
    }

    /**
     * gets recently changed issues.
     *
     * @param ConstructionSite $constructionSite
     * @param int $days
     *
     * @throws \Exception
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
}
