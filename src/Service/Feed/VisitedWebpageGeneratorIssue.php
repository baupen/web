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

use App\Entity\Craftsman;
use App\Service\Feed\Base\CraftsmanFeedEntryGenerator;
use App\Service\Feed\Entity\FeedEntry;

class VisitedWebpageGeneratorIssue implements CraftsmanFeedEntryGenerator
{
    /**
     * @param Craftsman[] $craftsmen
     *
     * @return FeedEntry[]
     */
    public function getFeedEntries($craftsmen)
    {
        //create feed entries
        $res = [];
        foreach ($craftsmen as $craftsman) {
            if (null !== $craftsman->getLastOnlineVisit()) {
                $feedEntry = new FeedEntry();
                $feedEntry->setTimestamp($craftsman->getLastOnlineVisit());
                $feedEntry->setCraftsman($craftsman);
                $res[] = $feedEntry;
            }
        }

        return $res;
    }
}
