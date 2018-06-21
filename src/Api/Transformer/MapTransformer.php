<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:20 PM
 */

namespace App\Api\Transformer;


use App\Api\Entity\Issue;
use App\Api\Transformer\Base\AbstractTransformer;
use App\Entity\Map;

class MapTransformer extends AbstractTransformer
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
     * @return \App\Api\Entity\Map
     */
    public function toApi(Map $entity)
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

    /**
     * @param Issue[] $entities
     * @return \App\Api\Entity\Issue[]
     */
    public function toApiMultiple(array $entities)
    {
        return parent::toApiMultipleInternal($entities, function ($entity) {
            return $this->toApi($entity);
        });
    }
}