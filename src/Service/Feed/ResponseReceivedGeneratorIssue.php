<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Feed;

use App\Entity\Issue;
use App\Service\Feed\Base\DailyEventRegistration;
use App\Service\Feed\Base\IssueFeedEntryGenerator;
use App\Service\Feed\Entity\FeedEntry;

class ResponseReceivedGeneratorIssue extends DailyEventRegistration implements IssueFeedEntryGenerator
{
    /**
     * @param Issue[] $issues
     *
     * @return FeedEntry[]
     */
    public function getFeedEntries($issues)
    {
        //register events
        foreach ($issues as $issue) {
            if (null !== $issue->getRespondedAt()) {
                $this->register($issue->getRespondedAt(), $issue->getResponseBy());
            }
        }

        //create feed entries
        $res = [];
        foreach ($this->getRegistrations() as $registration) {
            $feedEntry = new FeedEntry();
            $feedEntry->setTimestamp($registration[0]);
            $feedEntry->setCraftsman($registration[1]);
            $feedEntry->setCount($registration[2]);
            $res[] = $feedEntry;
        }

        return $res;
    }
}
