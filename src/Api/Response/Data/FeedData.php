<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data;

use App\Api\Entity\Feed\Feed;

class FeedData
{
    /** @var Feed */
    private $feed;

    /**
     * @return Feed
     */
    public function getFeed(): Feed
    {
        return $this->feed;
    }

    /**
     * @param Feed $feed
     */
    public function setFeed(Feed $feed): void
    {
        $this->feed = $feed;
    }
}
