<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Share\Filter;

use App\Entity\Issue;

class IssueTransformer
{
    /**
     * @var \App\Api\Transformer\Share\Base\IssueTransformer
     */
    private $issueTransformer;

    /**
     * CraftsmanTransformer constructor.
     *
     * @param \App\Api\Transformer\Share\Base\IssueTransformer $issueTransformer
     */
    public function __construct(\App\Api\Transformer\Share\Base\IssueTransformer $issueTransformer)
    {
        $this->issueTransformer = $issueTransformer;
    }

    /**
     * @param Issue $entity
     * @param string $identifier
     *
     * @return \App\Api\Entity\Share\Filter\Issue
     */
    public function toApi($entity, string $identifier)
    {
        $issue = new \App\Api\Entity\Share\Filter\Issue($entity->getId());
        $this->issueTransformer->writeApiProperties($entity, $issue, $identifier);
        if ($entity->getReviewedAt() !== null) {
            $issue->setReviewedAt($entity->getReviewedAt());
            $issue->setReviewedByName($entity->getReviewBy()->getName());
        }

        return $issue;
    }
}
