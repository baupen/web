<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Feed;

use App\Entity\Issue;
use App\Feed\Base\DailyEventRegistration;
use App\Feed\Base\IssueFeedEntryGenerator;
use App\Feed\Entity\FeedEntry;

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
            if ($issue->getRespondedAt() !== null) {
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
