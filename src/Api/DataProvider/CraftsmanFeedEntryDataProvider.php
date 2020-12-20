<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\DataProvider;

use App\Api\DataProvider\Base\FeedEntryDataProvider;
use App\Api\DataProvider\FeedEntryDataProvider\FeedEntryAggregator;
use App\Api\Entity\FeedEntry;
use App\Entity\Craftsman;

class CraftsmanFeedEntryDataProvider extends FeedEntryDataProvider
{
    protected function getResourceClass(): string
    {
        return Craftsman::class;
    }

    /**
     * @param Craftsman[] $resources
     */
    protected function registerEvents(array $resources, FeedEntryAggregator $aggregator)
    {
        foreach ($resources as $craftsman) {
            if ($craftsman->getLastOnlineVisit()) {
                $aggregator->register($craftsman->getLastOnlineVisit(), $craftsman, FeedEntry::TYPE_CRAFTSMAN_VISITED_WEBPAGE);
            }
        }
    }
}
