<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer;

use App\Api\Entity\ObjectMeta;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;

class ObjectMetaTransformer
{
    /**
     * @param IdTrait|TimeTrait $entity
     *
     * @return ObjectMeta
     */
    public function toApi($entity)
    {
        $meta = new ObjectMeta();
        $meta->setId($entity->getId());
        $meta->setLastChangeTime($entity->getLastChangedAt()->format('c'));

        return $meta;
    }
}
