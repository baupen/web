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

use App\Entity\Craftsman;
use App\Service\Feed\Entity\FeedEntry;

interface CraftsmanFeedEntryGenerator
{
    /**
     * @param Craftsman[] $craftsmen
     *
     * @return FeedEntry[]
     */
    public function getFeedEntries($craftsmen);
}
