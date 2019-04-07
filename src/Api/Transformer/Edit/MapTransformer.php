<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Edit;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\Map;

class MapTransformer extends BatchTransformer
{
    /**
     * @param Map $entity
     *
     * @throws \Exception
     *
     * @return \App\Api\Entity\Edit\Map
     */
    public function toApi($entity)
    {
        $map = new \App\Api\Entity\Edit\Map($entity->getId());
        $map->setName($entity->getName());
        $map->setCreatedAt($entity->getCreatedAt());
        $map->setParentId($entity->getParent() !== null ? $entity->getParent()->getId() : null);
        $map->setFileId($entity->getFile() !== null ? $entity->getFile()->getId() : null);
        $map->setIssueCount($entity->getIssues()->count());

        return $map;
    }
}
