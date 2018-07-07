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

        $issue->setImageShareView($this->router->generate('image_issue', ['issue' => $entity->getId(), 'size' => ImageServiceInterface::SIZE_SHARE_VIEW]));
        $issue->setImageFull($this->router->generate('image_issue', ['issue' => $entity->getId(), 'size' => ImageServiceInterface::SIZE_FULL]));

        return $issue;
    }
}
