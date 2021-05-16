<?php

/*
 * This file is part of the baupen project.
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
use App\Entity\Issue;

class IssueFeedEntryDataProvider extends FeedEntryDataProvider
{
    protected function getResourceClass(): string
    {
        return Issue::class;
    }

    /**
     * @param Issue[] $resources
     */
    protected function registerEvents(array $resources, FeedEntryAggregator $aggregator)
    {
        foreach ($resources as $issue) {
            if ($issue->getRegisteredAt() && $issue->getRegisteredBy()) {
                $aggregator->register($issue->getRegisteredAt(), $issue->getRegisteredBy(), FeedEntry::TYPE_CONSTRUCTION_MANAGER_REGISTERED);
            }
            if ($issue->getResolvedAt() && $issue->getResolvedBy()) {
                $aggregator->register($issue->getResolvedAt(), $issue->getResolvedBy(), FeedEntry::TYPE_CRAFTSMAN_RESOLVED);
            }
            if ($issue->getClosedAt() && $issue->getClosedBy()) {
                $aggregator->register($issue->getClosedAt(), $issue->getClosedBy(), FeedEntry::TYPE_CONSTRUCTION_MANAGER_CLOSED);
            }
        }
    }
}
