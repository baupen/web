<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:20 PM
 */

namespace App\Api\Transformer;

use App\Api\Entity\Address;
use App\Api\Entity\Building;
use App\Api\Transformer\Base\AbstractTransformer;
use App\Entity\ConstructionSite;

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
        $building->setImageFilename($entity->getImageFilePath());

        $childrenIds = [];
        foreach ($entity->getMaps() as $child) {
            $childrenIds[] = $child->getId();
        }
        $building->setMaps($childrenIds);

        $childrenIds = [];
        foreach ($entity->getCraftsmen() as $child) {
            $childrenIds[] = $child->getId();
        }
        $building->setCraftsmen($childrenIds);

        $address = new Address();
        $address->setStreetAddress($entity->getStreetAddress());
        $address->setLocality($entity->getLocality());
        $address->setPostalCode($entity->getPostalCode());
        $address->setCountry($entity->getCountry());
        $building->setAddress($address);

        $building->setMeta($this->objectMetaTransformer->toApi($entity));

        return $building;
    }
}
