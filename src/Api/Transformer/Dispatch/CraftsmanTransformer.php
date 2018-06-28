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
     *
     * @return \App\Api\Entity\Dispatch\Craftsman
     */
    public function toApi($entity)
    {
        $craftsman = new \App\Api\Entity\Dispatch\Craftsman($entity->getId());
        $this->craftsmanTransformer->writeProperties($entity, $craftsman);

        $craftsman->setLastEmailSent($entity->getLastEmailSent());
        $craftsman->setLastOnlineVisit($entity->getLastOnlineVisit());

        $nextResponseLimit = null;
        $unread = 0;
        $open = 0;
        foreach ($entity->getIssues() as $issue) {
            //if registered then valid
            if ($issue->getRegisteredAt() !== null) {
                if ($issue->getReviewedAt() === null) {
                    if ($issue->getRegisteredAt() > $craftsman->getLastOnlineVisit()) {
                        ++$unread;
                    }
                    ++$open;
                    if ($issue->getResponseLimit() !== null && ($nextResponseLimit === null || $issue->getResponseLimit() < $nextResponseLimit)) {
                        $nextResponseLimit = $issue->getResponseLimit();
                    }
                }
            }
        }

        $craftsman->setOpenIssuesCount($open);
        $craftsman->setUnreadIssuesCount($unread);
        $craftsman->setNextResponseLimit($nextResponseLimit);

        return $craftsman;
    }
}
