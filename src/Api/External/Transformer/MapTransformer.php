<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Transformer;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\Map;

class MapTransformer extends BatchTransformer
{
    /**
     * @var ObjectMetaTransformer
     */
    private $objectMetaTransformer;

    /**
     * @var FileTransformer
     */
    private $fileTransformer;

    public function __construct(ObjectMetaTransformer $objectMetaTransformer, FileTransformer $fileTransformer)
    {
        $this->objectMetaTransformer = $objectMetaTransformer;
        $this->fileTransformer = $fileTransformer;
    }

    /**
     * @param Map $entity
     *
     * @return \App\Api\External\Entity\Map
     */
    public function toApi($entity)
    {
        $map = new \App\Api\External\Entity\Map();
        $map->setFile($this->fileTransformer->toApi($entity->getFile()));
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
