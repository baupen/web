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
     *
     * @param \App\Api\Transformer\Base\IssueTransformer $issueTransformer
     * @param RouterInterface $router
     */
    public function __construct(\App\Api\Transformer\Base\IssueTransformer $issueTransformer, RouterInterface $router)
    {
        $this->issueTransformer = $issueTransformer;
        $this->router = $router;
    }

    /**
     * @param Issue $entity
     * @param \App\Api\Entity\Foyer\Issue $issue
     */
    public function writeApiProperties(Issue $entity, \App\Api\Entity\Foyer\Issue $issue)
    {
        $this->issueTransformer->writeApiProperties($entity, $issue);

        $issue->setMap($entity->getMap()->getName());
        $issue->setIsMarked($entity->getIsMarked());
        $issue->setWasAddedWithClient($entity->getWasAddedWithClient());
        if ($entity->getCraftsman() !== null) {
            $issue->setCraftsmanId($entity->getCraftsman()->getId());
        }
        $issue->setUploadedAt($entity->getUploadedAt());
        $issue->setUploadByName($entity->getUploadBy()->getName());

        $issue->setImageThumbnail($this->router->generate('image_issue', ['issue' => $entity->getId(), 'imageFilename' => $entity->getImageFilename(), 'size' => ImageServiceInterface::SIZE_THUMBNAIL]));
        $issue->setImageFull($this->router->generate('image_issue', ['issue' => $entity->getId(), 'imageFilename' => $entity->getImageFilename(), 'size' => ImageServiceInterface::SIZE_FULL]));
    }

    /**
     * @param Issue $entity
     * @param null $args
     *
     * @return \App\Api\Entity\Foyer\Issue
     */
    public function toApi($entity, $args = null)
    {
        $issue = new \App\Api\Entity\Foyer\Issue($entity->getId());
        $this->writeApiProperties($entity, $issue);

        return $issue;
    }

    /**
     * @param \App\Api\Entity\Foyer\Issue $issue
     * @param Issue $entity
     */
    public function fromApi(\App\Api\Entity\Foyer\Issue $issue, Issue $entity)
    {
        $entity->setIsMarked($issue->getIsMarked());
        $entity->setWasAddedWithClient($issue->getWasAddedWithClient());
        $this->issueTransformer->writeEntityProperties($issue, $entity);
    }
}
