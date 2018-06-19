<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:20 PM
 */

namespace App\Api\Transformer;


use App\Api\Entity\Issue;
use App\Api\Entity\ObjectMeta;
use App\Api\Entity\User;
use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Entity\Map;
use Symfony\Bridge\Doctrine\RegistryInterface;

class IssueTransformer
{
    /**
     * @var ObjectMetaTransformer
     */
    private $objectMetaTransformer;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    public function __construct(ObjectMetaTransformer $objectMetaTransformer, RegistryInterface $registry)
    {
        $this->objectMetaTransformer = $objectMetaTransformer;
        $this->doctrine = $registry;
    }

    /**
     * @param Issue $issue
     * @param \App\Entity\Issue $entity
     * @return \App\Entity\Issue|null
     */
    public function fromApi(Issue $issue, \App\Entity\Issue $entity)
    {
        $map = $this->doctrine->getRepository(Map::class)->findOneBy(["id" => $issue->getMap()]);
        if ($map == null) {
            return null;
        }

        $craftsman = null;
        if ($issue->getCraftsman() != "") {
            $craftsman = $this->doctrine->getRepository(Craftsman::class)->findOneBy(["id" => $issue->getCraftsman()]);
            if ($craftsman == null) {
                return null;
            }
        }

        $entity->setMap($map);
        $entity->setCraftsman($craftsman);
        $entity->setDescription($issue->getDescription());
        $entity->setImageFilename($issue->getImageFilename());
        $entity->setIsMarked($issue->isMarked());
        $entity->setWasAddedWithClient($issue->isWasAddedWithClient());

        if ($issue->getPosition() != null) {
            $entity->setPositionX($issue->getPosition()->getX());
            $entity->setPositionY($issue->getPosition()->getY());
            $entity->setPositionZoomScale($issue->getPosition()->getZoomScale());
        } else {
            $entity->setPositionX(null);
            $entity->setPositionY(null);
            $entity->setPositionZoomScale(null);
        }
        return $entity;
    }
}