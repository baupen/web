<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Feed;

class Feed
{
    /**
     * @var FeedEntry[]
     */
    private $entries;

    /**
     * @return FeedEntry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * @param FeedEntry[] $entries
     */
    public function setEntries(array $entries): void
    {
        $this->entries = $entries;
    }
}
