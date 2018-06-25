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

abstract class BatchTransformer
{
    public abstract function toApi($entity);

    /**
     * @param array    $entities
     *
     * @return array
     */
    protected function toApiMultipleInternal(array $entities)
    {
        $res = [];
        foreach ($entities as $entity) {
            $res[] = $this->toApi($entity);
        }

        return $res;
    }
}
