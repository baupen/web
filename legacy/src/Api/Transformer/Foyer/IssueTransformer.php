<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Foyer;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\Issue;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\Routing\RouterInterface;

class IssueTransformer extends BatchTransformer
{
    /**
     * @var \App\Api\Transformer\Base\IssueTransformer
     */
    private $issueTransformer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * CraftsmanTransformer constructor.
     */
    public function __construct(\App\Api\Transformer\Base\IssueTransformer $issueTransformer, RouterInterface $router)
    {
        $this->issueTransformer = $issueTransformer;
        $this->router = $router;
    }

    public function writeApiProperties(Issue $entity, \App\Api\Entity\Foyer\Issue $issue)
    {
        $this->issueTransformer->writeApiProperties($entity, $issue);

        $issue->setResponseLimit($entity->getResponseLimit());
        $issue->setMap($entity->getMap()->getNameWithContext());
        $issue->setIsMarked($entity->getIsMarked());
        $issue->setWasAddedWithClient($entity->getWasAddedWithClient());
        if (null !== $entity->getCraftsman()) {
            $issue->setCraftsmanId($entity->getCraftsman()->getId());
        }
        $issue->setUploadedAt($entity->getUploadedAt());
        $issue->setUploadByName($entity->getUploadBy()->getName());

        if (null !== $entity->getImage()) {
            $issue->setImageThumbnail($this->router->generate('image_issue', ['issue' => $entity->getId(), 'image' => $entity->getImage()->getId(), 'size' => ImageServiceInterface::SIZE_THUMBNAIL]));
            $issue->setImageFull($this->router->generate('image_issue', ['issue' => $entity->getId(), 'image' => $entity->getImage()->getId(), 'size' => ImageServiceInterface::SIZE_FULL]));
        }
    }

    /**
     * @param Issue $entity
     *
     * @return \App\Api\Entity\Foyer\Issue
     */
    public function toApi($entity)
    {
        $issue = new \App\Api\Entity\Foyer\Issue($entity->getId());
        $this->writeApiProperties($entity, $issue);

        return $issue;
    }

    public function fromApi(\App\Api\Entity\Foyer\Issue $issue, Issue $entity)
    {
        $entity->setIsMarked($issue->getIsMarked());
        $entity->setWasAddedWithClient($issue->getWasAddedWithClient());
        $entity->setResponseLimit($issue->getResponseLimit());
        $this->issueTransformer->writeEntityProperties($issue, $entity);
    }
}
