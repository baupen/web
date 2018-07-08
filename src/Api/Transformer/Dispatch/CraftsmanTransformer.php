<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Dispatch;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\Craftsman;
use App\Model\Craftsman\CurrentIssueState;

class CraftsmanTransformer extends BatchTransformer
{
    /**
     * @var \App\Api\Transformer\Base\CraftsmanTransformer
     */
    private $craftsmanTransformer;

    /**
     * CraftsmanTransformer constructor.
     *
     * @param \App\Api\Transformer\Base\CraftsmanTransformer $craftsmanTransformer
     */
    public function __construct(\App\Api\Transformer\Base\CraftsmanTransformer $craftsmanTransformer)
    {
        $this->craftsmanTransformer = $craftsmanTransformer;
    }

    /**
     * @param Craftsman $entity
     * @param null $args
     *
     * @return \App\Api\Entity\Dispatch\Craftsman
     */
    public function toApi($entity, $args = null)
    {
        $craftsman = new \App\Api\Entity\Dispatch\Craftsman($entity->getId());
        $this->craftsmanTransformer->writeApiProperties($entity, $craftsman);

        $craftsman->setLastEmailSent($entity->getLastEmailSent());
        $craftsman->setLastOnlineVisit($entity->getLastOnlineVisit());

        $state = new CurrentIssueState($entity, new \DateTime());
        $craftsman->setNotRespondedIssuesCount($state->getNotRespondedIssuesCount());
        $craftsman->setNotReadIssuesCount($state->getNotReadIssuesCount());
        $craftsman->setNextResponseLimit($state->getNextResponseLimit());

        return $craftsman;
    }
}
