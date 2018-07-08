<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Base;

use App\Api\Entity\Base\BaseEntity;
use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\Traits\IdTrait;

class BaseEntityTransformer extends BatchTransformer
{
    /**
     * @param IdTrait $entity
     * @param null $args
     *
     * @return BaseEntity
     */
    public function toApi($entity, $args = null)
    {
        return new BaseEntity($entity->getId());
    }
}
