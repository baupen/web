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

use App\Api\Transformer\Base\BatchTransformer;
use App\Entity\Map;

class MapTransformer extends BatchTransformer
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
     * @param Map $entity
     *
     * @return \App\Api\Entity\Map
     */
    public function toApi($entity)
    {
        $map = new \App\Api\Entity\Map();
        $map->setFilename($entity->getFilename());
        $map->setName($entity->getName());

        $issueIds = [];
        foreach ($entity->getIssues() as $issue) {
            $issueIds[] = $issue->getId();
        }
        $map->setIssues($issueIds);

        $childrenIds = [];
        foreach ($entity->getChildren() as $child) {
            $childrenIds[] = $child->getId();
        }
        $map->setChildren($childrenIds);

        $map->setMeta($this->objectMetaTransformer->toApi($entity));

        return $map;
    }
}
