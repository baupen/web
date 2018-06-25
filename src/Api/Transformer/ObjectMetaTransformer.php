<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:09 PM
 */

namespace App\Api\Transformer;

use App\Api\Entity\ObjectMeta;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;

class ObjectMetaTransformer
{
    /**
     * @param IdTrait|TimeTrait $entity
     * @return ObjectMeta
     */
    public function toApi($entity)
    {
        $meta = new ObjectMeta();
        $meta->setId($entity->getId());
        $meta->setLastChangeTime($entity->getLastChangedAt()->format("c"));
        return $meta;
    }
}
