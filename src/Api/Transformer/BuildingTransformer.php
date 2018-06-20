<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:20 PM
 */

namespace App\Api\Transformer;


use App\Api\Entity\Building;
use App\Api\Entity\Issue;
use App\Api\Entity\ObjectMeta;
use App\Api\Entity\User;
use App\Api\Transformer\Base\AbstractTransformer;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Map;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BuildingTransformer extends AbstractTransformer
{
    /**
     * @var ObjectMetaTransformer
     */
    private $objectMetaTransformer;


    public function __construct(ObjectMetaTransformer $objectMetaTransformer)
    {
        $this->objectMetaTransformer = $objectMetaTransformer;
    }

    /**
     * @param ConstructionSite[] $entities
     * @return Building[]
     */
    public function toApiMultiple(array $entities)
    {
        return parent::toApiMultipleInternal($entities, function ($entity) {
            return $this->toApi($entity);
        });
    }

    /**
     * @param ConstructionSite $entity
     * @return Building
     */
    public function toApi(ConstructionSite $entity)
    {
        $building = new Building();
        $building->setName($entity->getName());
        $building->setMeta($this->objectMetaTransformer->toApi($entity));
        return $building;
    }
}