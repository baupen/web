<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Register;

use App\Entity\Issue;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Routing\RouterInterface;

class UpdateIssueTransformer
{
    /**
     * @var \App\Api\Transformer\Foyer\UpdateIssueTransformer
     */
    private $updateIssueTransformer;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * CraftsmanTransformer constructor.
     *
     * @param \App\Api\Transformer\Foyer\UpdateIssueTransformer $updateIssueTransformer
     * @param RouterInterface $router
     * @param RegistryInterface $registry
     */
    public function __construct(\App\Api\Transformer\Foyer\UpdateIssueTransformer $updateIssueTransformer, RouterInterface $router, RegistryInterface $registry)
    {
        $this->updateIssueTransformer = $updateIssueTransformer;
        $this->doctrine = $registry;
    }

    /**
     * @param \App\Api\Entity\Register\UpdateIssue $issue
     * @param Issue $entity
     * @param callable $validateCraftsman
     *
     * @return bool
     */
    public function fromApi(\App\Api\Entity\Register\UpdateIssue $issue, Issue $entity, $validateCraftsman)
    {
        $this->updateIssueTransformer->fromApi($issue, $entity, $validateCraftsman);

        return true;
    }
}
