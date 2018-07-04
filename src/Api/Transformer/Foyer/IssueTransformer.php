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

class IssueTransformer extends BatchTransformer
{
    /**
     * @var \App\Api\Transformer\Base\IssueTransformer
     */
    private $issueTransformer;

    /**
     * CraftsmanTransformer constructor.
     *
     * @param \App\Api\Transformer\Base\IssueTransformer $issueTransformer
     */
    public function __construct(\App\Api\Transformer\Base\IssueTransformer $issueTransformer)
    {
        $this->issueTransformer = $issueTransformer;
    }

    /**
     * @param Issue $entity
     *
     * @return \App\Api\Entity\Foyer\Issue
     */
    public function toApi($entity)
    {
        $issue = new \App\Api\Entity\Foyer\Issue($entity->getId());
        $this->issueTransformer->writeApiProperties($entity, $issue);

        $issue->setMap($entity->getMap()->getName());
        $issue->setIsMarked($entity->getIsMarked());
        $issue->setWasAddedWithClient($entity->getWasAddedWithClient());
        if ($entity->getCraftsman() !== null) {
            $issue->setCraftsmanId($entity->getCraftsman()->getId());
        }
        $issue->setUploadedAt($entity->getUploadedAt());
        $issue->setUploadByName($entity->getUploadBy()->getName());

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
