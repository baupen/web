<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Share;

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
     * @return \App\Api\Entity\Share\Issue
     */
    public function toApi($entity)
    {
        $issue = new \App\Api\Entity\Share\Issue($entity->getId());
        $this->issueTransformer->writeApiProperties($entity, $issue);

        $issue->setRegisteredAt($entity->getRegisteredAt());
        $issue->setRegistrationByName($entity->getRegistrationBy()->getName());
        $issue->setNumber($entity->getNumber());

        return $issue;
    }
}
