<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Share\Base;

use App\Api\Entity\Base\PublicIssue;
use App\Entity\Issue;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\Routing\RouterInterface;

class IssueTransformer
{
    /**
     * @var \App\Api\Transformer\Base\PublicIssueTransformer
     */
    private $issueTransformer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * CraftsmanTransformer constructor.
     *
     * @param \App\Api\Transformer\Base\PublicIssueTransformer $issueTransformer
     * @param RouterInterface $router
     */
    public function __construct(\App\Api\Transformer\Base\PublicIssueTransformer $issueTransformer, RouterInterface $router)
    {
        $this->issueTransformer = $issueTransformer;
        $this->router = $router;
    }

    /**
     * @param Issue $entity
     * @param PublicIssue $issue
     * @param string $identifier
     *
     * @return PublicIssue
     */
    public function writeApiProperties($entity, $issue, $identifier)
    {
        $this->issueTransformer->writeApiProperties($entity, $issue);

        if ($entity->getImage() !== null) {
            $routeArguments = ['identifier' => $identifier, 'imageId' => $entity->getImage()->getId(), 'issue' => $entity->getId()];
            $issue->setImageShareView($this->router->generate('external_image_craftsman_issue', $routeArguments + ['size' => ImageServiceInterface::SIZE_SHARE_VIEW]));
            $issue->setImageFull($this->router->generate('external_image_craftsman_issue', $routeArguments + ['size' => ImageServiceInterface::SIZE_FULL]));
        }

        return $issue;
    }
}
