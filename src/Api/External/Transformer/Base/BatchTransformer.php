<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Transformer\Base;

abstract class BatchTransformer
{
    /**
     * @param $entity
     *
     * @return mixed
     */
    abstract public function toApi($entity);

    /**
     * transforms all toApi, preserving the ordering.
     *
     * @param array $entities
     *
     * @return array
     */
    public function toApiMultiple(array $entities)
    {
        $res = [];
        foreach ($entities as $entity) {
            $res[] = $this->toApi($entity);
        }

        return $res;
    }
}
