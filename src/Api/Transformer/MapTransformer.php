<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:20 PM
 */

namespace App\Api\Transformer;


use App\Api\Entity\Building;
use App\Api\Entity\Issue;
use App\Api\Entity\ObjectMeta;
use App\Api\Entity\User;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Map;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MapTransformer
{
    /**
     * @var ObjectMetaTransformer
     */
    private $objectMetaTransformer;


    public function __construct(ObjectMetaTransformer $objectMetaTransformer)
    {
        $this->objectMetaTransformer = $objectMetaTransformer;
    }

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
        return $entity;
    }
}