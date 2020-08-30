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
     * @var FileTransformer
     */
    private $fileTransformer;

    /**
     * @var IssuePositionTransformer
     */
    private $issuePositionTransformer;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    public function __construct(ObjectMetaTransformer $objectMetaTransformer, FileTransformer $fileTransformer, IssuePositionTransformer $issuePositionTransformer, RegistryInterface $registry)
    {
        $this->objectMetaTransformer = $objectMetaTransformer;
        $this->doctrine = $registry;
        $this->fileTransformer = $fileTransformer;
        $this->issuePositionTransformer = $issuePositionTransformer;
    }

    /**
     * @return Issue
     */
    public function fromApi(\App\Api\External\Entity\Issue $issue, Issue $entity)
    {
        $entity->setDescription($issue->getDescription());
        $entity->setIsMarked($issue->getIsMarked());
        $entity->setWasAddedWithClient($issue->getWasAddedWithClient());
        $entity->setPosition($this->issuePositionTransformer->fromApi($issue->getPosition(), $entity));

        return $entity;
    }

    /**
     * @param Issue $entity
     *
     * @return \App\Api\External\Entity\Issue
     */
    public function toApi($entity)
    {
        $issue = new \App\Api\External\Entity\Issue();
        $issue->setWasAddedWithClient($entity->getWasAddedWithClient());
        $issue->setIsMarked($entity->getIsMarked());
        $issue->setImage($this->fileTransformer->toApi($entity->getImage()));
        $issue->setDescription($entity->getDescription());
        $issue->setNumber($entity->getNumber());
        $issue->setPosition($this->issuePositionTransformer->toApi($entity->getPosition()));

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
