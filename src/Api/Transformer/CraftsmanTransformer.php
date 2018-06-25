<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:20 PM
 */

namespace App\Api\Transformer;

use App\Api\Transformer\Base\AbstractTransformer;
use App\Entity\Craftsman;

class CraftsmanTransformer extends AbstractTransformer
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
     * @param Craftsman $entity
     * @return \App\Api\Entity\Craftsman
     */
    public function toApi(Craftsman $entity)
    {
        $craftsman = new \App\Api\Entity\Craftsman();
        $craftsman->setName($entity->getName());
        $craftsman->setTrade($entity->getTrade());

        $craftsman->setMeta($this->objectMetaTransformer->toApi($entity));
        return $craftsman;
    }

    /**
     * @param Craftsman[] $entities
     * @return \App\Api\Entity\Craftsman[]
     */
    public function toApiMultiple(array $entities)
    {
        return parent::toApiMultipleInternal($entities, function ($entity) {
            return $this->toApi($entity);
        });
    }
}
