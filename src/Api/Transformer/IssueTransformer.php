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

use App\Api\Entity\IssuePosition;
use App\Api\Entity\IssueStatus;
use App\Api\Entity\IssueStatusEvent;
use App\Api\Transformer\Base\BatchTransformer;
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
     * @param \App\Api\Entity\Issue $issue
     * @param \App\Entity\Issue $entity
     *
     * @return \App\Entity\Issue
     */
    public function fromApi(\App\Api\Entity\Issue $issue, Issue $entity)
    {
        $entity->setDescription($issue->getDescription());
        $entity->setImageFilename($issue->getImageFilename());
        $entity->setIsMarked($issue->getIsMarked());
        $entity->setWasAddedWithClient($issue->getWasAddedWithClient());

        if (null !== $issue->getPosition()) {
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
     *
     * @return \App\Api\Entity\Issue
     */
    public function toApi($entity)
    {
        $issue = new \App\Api\Entity\Issue();
        $issue->setWasAddedWithClient($entity->getWasAddedWithClient());
        $issue->setIsMarked($entity->getIsMarked());
        $issue->setImageFilename($entity->getImageFilename());
        $issue->setDescription($entity->getDescription());
        $issue->setNumber($entity->getNumber());

        if (null !== $entity->getPositionZoomScale()) {
            $issuePosition = new IssuePosition();
            $issuePosition->setZoomScale($entity->getPositionZoomScale());
            $issuePosition->setY($entity->getPositionY());
            $issuePosition->setX($entity->getPositionX());
            $issue->setPosition($issuePosition);
        }

        $issueStatus = new IssueStatus();
        if (null !== $entity->getRegisteredAt()) {
            $issueStatusEvent = new IssueStatusEvent();
            $issueStatusEvent->setAuthor($entity->getRegistrationBy()->getName());
            $issueStatusEvent->setTime($entity->getRegisteredAt()->format('c'));
            $issueStatus->setRegistration($issueStatusEvent);
        }
        if (null !== $entity->getRespondedAt()) {
            $issueStatusEvent = new IssueStatusEvent();
            $issueStatusEvent->setAuthor($entity->getResponseBy()->getName());
            $issueStatusEvent->setTime($entity->getRespondedAt()->format('c'));
            $issueStatus->setResponse($issueStatusEvent);
        }
        if (null !== $entity->getReviewedAt()) {
            $issueStatusEvent = new IssueStatusEvent();
            $issueStatusEvent->setAuthor($entity->getReviewBy()->getName());
            $issueStatusEvent->setTime($entity->getReviewedAt()->format('c'));
            $issueStatus->setReview($issueStatusEvent);
        }
        $issue->setStatus($issueStatus);

        $issue->setMap($entity->getMap()->getId());
        if (null !== $entity->getCraftsman()) {
            $issue->setCraftsman($entity->getCraftsman()->getId());
        }

        $issue->setMeta($this->objectMetaTransformer->toApi($entity));

        return $issue;
    }
}
