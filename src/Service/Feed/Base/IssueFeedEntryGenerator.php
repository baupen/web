<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Feed\Base;

use App\Entity\Issue;
use App\Service\Feed\Entity\FeedEntry;

interface IssueFeedEntryGenerator
{
    /**
     * @param Issue[] $issues
     *
     * @return FeedEntry[]
     */
    public function getFeedEntries($issues);
}
