<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/20/18
 * Time: 11:35 AM
 */

namespace App\Api\Transformer\Base;


use App\Entity\Traits\IdTrait;

abstract class AbstractTransformer
{
    /**
     * @param array $entities
     * @param callable $toAiMethod
     * @return array
     */
    protected function toApiMultipleInternal(array $entities, $toAiMethod)
    {
        $res = [];
        foreach ($entities as $entity) {
            $res[] = $toAiMethod($entity);
        }
        return $res;
    }
}