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
use App\Entity\Map;
use Doctrine\ORM\EntityRepository;

class MapRepository extends EntityRepository
{
    public function findTopLevelMaps(ConstructionSite $constructionSite)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('m')
            ->from(Map::class, 'm')
            ->leftJoin('m.parent', 'p')
            ->where('m.constructionSite = :construction_site_id')
            ->setParameter(':construction_site_id', $constructionSite->getId())
            ->orderBy('m.name', 'ASC')
        ;

        $maps = $queryBuilder->getQuery()->getResult();

        $topLevelMaps = [];
        foreach ($maps as $map) {
            if (null === $map->getParent()) {
                $topLevelMaps[] = $map;
            }
        }

        return $topLevelMaps;
    }
}
