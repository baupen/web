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
use Symfony\Component\Routing\RouterInterface;

class IssueTransformer
{
    /**
     * @var \App\Api\Transformer\Share\Base\IssueTransformer
     */
    private $issueTransformer;

    /**
     * CraftsmanTransformer constructor.
     */
    public function __construct(\App\Api\Transformer\Share\Base\IssueTransformer $issueTransformer)
    {
        $this->issueTransformer = $issueTransformer;
    }

    /**
     * @param Issue $entity
     *
     * @return \App\Api\Entity\Share\Filter\Issue
     */
    public function toApi($entity, string $identifier)
    {
        $issue = new \App\Api\Entity\Share\Filter\Issue($entity->getId());
        $this->issueTransformer->writeApiProperties($entity, $issue, $identifier, function ($router, $arguments) {
            /* @var RouterInterface $router */
            return $router->generate('external_image_filter_issue', $arguments);
        });

        if ($entity->getReviewedAt() !== null) {
            $issue->setReviewedAt($entity->getReviewedAt());
            $issue->setReviewedByName($entity->getReviewBy()->getName());
        }

        return $issue;
    }
}
