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

        //TODO: similar code when sending the mail; consider merging it
        $nextResponseLimit = null;
        $unread = 0;
        $open = 0;
        foreach ($entity->getIssues() as $issue) {
            //if registered then valid
            if ($issue->getRegisteredAt() !== null) {
                if ($issue->getReviewedAt() === null) {
                    if ($craftsman->getLastOnlineVisit() === null || $issue->getRegisteredAt() > $craftsman->getLastOnlineVisit()) {
                        ++$unread;
                    }
                    ++$open;
                    if ($issue->getResponseLimit() !== null && ($nextResponseLimit === null || $issue->getResponseLimit() < $nextResponseLimit)) {
                        $nextResponseLimit = $issue->getResponseLimit();
                    }
                }
            }
        }

        $craftsman->setNotRespondedIssuesCount($open);
        $craftsman->setNoteReadIssuesCount($unread);
        $craftsman->setNextResponseLimit($nextResponseLimit);

        return $craftsman;
    }
}
