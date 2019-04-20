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

use App\Api\Entity\Foyer\UpdateIssue;
use App\Entity\Craftsman;
use App\Entity\Issue;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UpdateIssueTransformer
{
    /**
     * @var \App\Api\Transformer\Base\IssueTransformer
     */
    private $issueTransformer;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * CraftsmanTransformer constructor.
     *
     * @param \App\Api\Transformer\Base\IssueTransformer $issueTransformer
     * @param RegistryInterface $registry
     */
    public function __construct(\App\Api\Transformer\Base\IssueTransformer $issueTransformer, RegistryInterface $registry)
    {
        $this->issueTransformer = $issueTransformer;
        $this->doctrine = $registry;
    }

    /**
     * @param UpdateIssue $issue
     * @param Issue $entity
     * @param callable $validateCraftsman
     *
     * @return bool
     */
    public function fromApi(UpdateIssue $issue, Issue $entity, $validateCraftsman)
    {
        $entity->setIsMarked($issue->getIsMarked());
        $entity->setResponseLimit($issue->getResponseLimit());

        //get craftsman
        if ($issue->getCraftsmanId() !== null) {
            $craftsman = $this->doctrine->getRepository(Craftsman::class)->find($issue->getCraftsmanId());
            $res = $validateCraftsman($craftsman, $entity);
            if ($res !== true) {
                return $res;
            }
            $entity->setCraftsman($craftsman);
        }

        $this->issueTransformer->writeEntityProperties($issue, $entity);

        return true;
    }
}
