<?php

namespace App\Repository;

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Helper\DoctrineHelper;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class ConstructionManagerRepository extends EntityRepository
{
    private const string INVOLVEMENT_SUBQUERY = '
SELECT construction_manager_id as id, construction_site_id FROM construction_site_construction_manager
UNION SELECT created_by_id as id, construction_site_id FROM issue
UNION SELECT registered_by_id as id, construction_site_id FROM issue
UNION SELECT closed_by_id as id, construction_site_id FROM issue
UNION SELECT created_by as id, construction_site_id FROM issue_event
UNION SELECT created_by_id as id, construction_site_id FROM task
UNION SELECT closed_by_id as id, construction_site_id FROM task';

    /**
     * @param string[] $constructionSiteIds
     *
     * @return string[]
     */
    public function getRelatedConstructionManagers(array $constructionSiteIds): array
    {
        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('id', 'id');
        $query = $this->getEntityManager()->createNativeQuery(
            '
SELECT id FROM (' . self::INVOLVEMENT_SUBQUERY . ') as Involvement
WHERE construction_site_id IN (:construction_site_ids) AND id IS NOT NULL;',
            $rsm
        );
        $query->setParameter(':construction_site_ids', array_values($constructionSiteIds));

        return $query->getSingleColumnResult();
    }
}
