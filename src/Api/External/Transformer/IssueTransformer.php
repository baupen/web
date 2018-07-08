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

use App\Api\External\Entity\IssuePosition;
use App\Api\External\Entity\IssueStatus;
use App\Api\External\Entity\IssueStatusEvent;
use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\Issue;
use Symfony\Bridge\Doctrine\RegistryInterface;

class IssueTransformer extends BatchTransformer
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
     * @param \App\Api\External\Entity\Issue $issue
     * @param \App\Entity\Issue $entity
     *
     * @return \App\Entity\Issue
     */
    public function fromApi(\App\Api\External\Entity\Issue $issue, Issue $entity)
    {
        $entity->setDescription($issue->getDescription());
        $entity->setImageFilename($issue->getImageFilename());
        $entity->setIsMarked($issue->getIsMarked());
        $entity->setWasAddedWithClient($issue->getWasAddedWithClient());

        if ($issue->getPosition() !== null) {
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

    /**
     * @param Issue $entity
     * @param null $args
     *
     * @return \App\Api\External\Entity\Issue
     */
    public function toApi($entity, $args = null)
    {
        $issue = new \App\Api\External\Entity\Issue();
        $issue->setWasAddedWithClient($entity->getWasAddedWithClient());
        $issue->setIsMarked($entity->getIsMarked());
        $issue->setImageFilename($entity->getImageFilename());
        $issue->setDescription($entity->getDescription());
        $issue->setNumber($entity->getNumber());

        if ($entity->getPositionZoomScale() !== null) {
            $issuePosition = new IssuePosition();
            $issuePosition->setZoomScale($entity->getPositionZoomScale());
            $issuePosition->setY($entity->getPositionY());
            $issuePosition->setX($entity->getPositionX());
            $issue->setPosition($issuePosition);
        }

        $issueStatus = new IssueStatus();
        if ($entity->getRegisteredAt() !== null) {
            $issueStatusEvent = new IssueStatusEvent();
            $issueStatusEvent->setAuthor($entity->getRegistrationBy()->getName());
            $issueStatusEvent->setTime($entity->getRegisteredAt()->format('c'));
            $issueStatus->setRegistration($issueStatusEvent);
        }
        if ($entity->getRespondedAt() !== null) {
            $issueStatusEvent = new IssueStatusEvent();
            $issueStatusEvent->setAuthor($entity->getResponseBy()->getName());
            $issueStatusEvent->setTime($entity->getRespondedAt()->format('c'));
            $issueStatus->setResponse($issueStatusEvent);
        }
        if ($entity->getReviewedAt() !== null) {
            $issueStatusEvent = new IssueStatusEvent();
            $issueStatusEvent->setAuthor($entity->getReviewBy()->getName());
            $issueStatusEvent->setTime($entity->getReviewedAt()->format('c'));
            $issueStatus->setReview($issueStatusEvent);
        }
        $issue->setStatus($issueStatus);

        $issue->setMap($entity->getMap()->getId());
        if ($entity->getCraftsman() !== null) {
            $issue->setCraftsman($entity->getCraftsman()->getId());
        }

        $issue->setMeta($this->objectMetaTransformer->toApi($entity));

        return $issue;
    }
}
