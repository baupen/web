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
     * @param callable $generateRoute
     *
     * @return PublicIssue
     */
    public function writeApiProperties($entity, $issue, $identifier, callable $generateRoute)
    {
        $this->issueTransformer->writeApiProperties($entity, $issue);

        if ($entity->getImage() !== null) {
            $routeArguments = ['identifier' => $identifier, 'image' => $entity->getImage()->getId(), 'issue' => $entity->getId()];
            $issue->setImageShareView($generateRoute($this->router, $routeArguments + ['size' => ImageServiceInterface::SIZE_SHARE_VIEW]));
            $issue->setImageShareView($generateRoute($this->router, $routeArguments + ['size' => ImageServiceInterface::SIZE_FULL]));
        }

        return $issue;
    }
}
