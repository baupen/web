<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 3:20 PM
 */

namespace App\Api\Transformer;

use App\Api\Entity\IssuePosition;
use App\Api\Entity\IssueStatus;
use App\Api\Entity\IssueStatusEvent;
use App\Api\Transformer\Base\AbstractTransformer;
use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Entity\Map;
use Symfony\Bridge\Doctrine\RegistryInterface;

class IssueTransformer extends AbstractTransformer
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
     * @param \App\Api\Entity\Issue $issue
     * @param \App\Entity\Issue $entity
     * @return \App\Entity\Issue|null
     */
    public function fromApi(\App\Api\Entity\Issue $issue, Issue $entity)
    {
        $entity->setDescription($issue->getDescription());
        $entity->setImageFilename($issue->getImageFilename());
        $entity->setIsMarked($issue->getIsMarked());
        $entity->setWasAddedWithClient($issue->getWasAddedWithClient());

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

    public function toApi(Issue $entity)
    {
        $issue = new \App\Api\Entity\Issue();
        $issue->setWasAddedWithClient($entity->getWasAddedWithClient());
        $issue->setIsMarked($entity->getIsMarked());
        $issue->setImageFilename($entity->getImageFilename());
        $issue->setDescription($entity->getDescription());
        $issue->setNumber($entity->getNumber());

        if ($entity->getPositionZoomScale() != null) {
            $issuePosition = new IssuePosition();
            $issuePosition->setZoomScale($entity->getPositionZoomScale());
            $issuePosition->setY($entity->getPositionY());
            $issuePosition->setX($entity->getPositionX());
            $issue->setPosition($issuePosition);
        }

        $issueStatus = new IssueStatus();
        if ($entity->getRegisteredAt() != null) {
            $issueStatusEvent = new IssueStatusEvent();
            $issueStatusEvent->setAuthor($entity->getRegistrationBy()->getName());
            $issueStatusEvent->setTime($entity->getRegisteredAt()->format("c"));
            $issueStatus->setRegistration($issueStatusEvent);
        }
        if ($entity->getRespondedAt() != null) {
            $issueStatusEvent = new IssueStatusEvent();
            $issueStatusEvent->setAuthor($entity->getResponseBy()->getName());
            $issueStatusEvent->setTime($entity->getRespondedAt()->format("c"));
            $issueStatus->setResponse($issueStatusEvent);
        }
        if ($entity->getReviewedAt() != null) {
            $issueStatusEvent = new IssueStatusEvent();
            $issueStatusEvent->setAuthor($entity->getReviewBy()->getName());
            $issueStatusEvent->setTime($entity->getReviewedAt()->format("c"));
            $issueStatus->setReview($issueStatusEvent);
        }
        $issue->setStatus($issueStatus);

        $issue->setMap($entity->getMap()->getId());
        if ($entity->getCraftsman() != null) {
            $issue->setCraftsman($entity->getCraftsman()->getId());
        }

        $issue->setMeta($this->objectMetaTransformer->toApi($entity));
        return $issue;
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
