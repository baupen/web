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

use App\Entity\Issue;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class IssueRepository extends EntityRepository
{
    public function assignHighestNumber(Issue $issue)
    {
        /**
         * execute query like:
         * UPDATE issue
         * SET number = (SELECT COALESCE(MAX(number),0) + 1 FROM issue WHERE construction_site_id = '4CEA314D-3062-499C-8BAC-64E61652AA31')
         * WHERE id = '0207A5A1-C345-4781-B78E-E8962DDA599F'.
         *
         * Have to retry due to "Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction"
         */
        $retries = 0;
        $lastException = null;
        while ($retries++ < 10) {
            try {
                $select = $this->createQueryBuilder('i2')
                    ->select('(COALESCE(MAX(i2.number), 0) + 1)')
                    ->where('i2.constructionSite = :constructionSiteId');

                $update = $this->createQueryBuilder('i')
                    ->update()
                    ->set('i.number', '('.$select->getQuery()->getDQL().')')
                    ->where('i.id = :id')
                    ->setParameter(':id', $issue->getId())
                    ->setParameter(':constructionSiteId', $issue->getConstructionSite()->getId()); // for subquery

                $update->getQuery()->execute();

                $this->getEntityManager()->refresh($issue);

                return $issue->getNumber();
            } catch (\Exception $exception) {
                $lastException = $exception;

                // backoff to allow other threads to complete
                $randomBackoff = rand(0, 10) * $retries + 5;
                usleep($randomBackoff);
            }
        }

        throw new \Exception('too many retries', 0, $lastException);
    }

    public function filterNewIssues(string $rootAlias, QueryBuilder $builder): QueryBuilder
    {
        $builder->andWhere($rootAlias.'.registeredAt IS NULL');

        return $builder;
    }

    public function filterOpenIssues(string $rootAlias, QueryBuilder $builder): QueryBuilder
    {
        $builder->andWhere($rootAlias.'.registeredAt IS NOT NULL')
            ->andWhere($rootAlias.'.resolvedAt IS NULL')
            ->andWhere($rootAlias.'.closedAt IS NULL');

        return $builder;
    }

    public function filterInspectableIssues(string $rootAlias, QueryBuilder $builder): QueryBuilder
    {
        $builder->andWhere($rootAlias.'.resolvedAt IS NOT NULL')
            ->andWhere($rootAlias.'.closedAt IS NULL');

        return $builder;
    }

    public function filterClosedIssues(string $rootAlias, QueryBuilder $builder): QueryBuilder
    {
        $builder->andWhere($rootAlias.'.closedAt IS NOT NULL');

        return $builder;
    }
}
