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

    /**
     * @var MapSectorTransformer
     */
    private $mapSectorTransformer;

    /**
     * @var FrameTransformer
     */
    private $frameTransformer;

    public function __construct(ObjectMetaTransformer $objectMetaTransformer, FileTransformer $fileTransformer, MapSectorTransformer $mapSectorTransformer, FrameTransformer $frameTransformer)
    {
        $this->objectMetaTransformer = $objectMetaTransformer;
        $this->fileTransformer = $fileTransformer;
        $this->mapSectorTransformer = $mapSectorTransformer;
        $this->frameTransformer = $frameTransformer;
    }

    /**
     * @param Map $entity
     *
     * @return \App\Api\External\Entity\Map
     */
    public function toApi($entity)
    {
        $map = new \App\Api\External\Entity\Map();
        $map->setName($entity->getName());
        $map->setFile($this->fileTransformer->toApi($entity->getFile()));
        $map->setSectors($entity->getFile() !== null ? $this->mapSectorTransformer->toApiMultiple($entity->getFile()->getSectors()->toArray()) : []);
        $map->setSectorFrame($entity->getFile() !== null ? $this->frameTransformer->toApi($entity->getFile()->getSectorFrame()) : null);
        $map->setParentID($entity->getParent() !== null ? $entity->getParent()->getId() : null);
        $map->setConstructionSiteID($entity->getConstructionSite()->getId());

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
