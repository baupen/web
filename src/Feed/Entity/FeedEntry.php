<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Feed\Entity;

use App\Entity\Craftsman;

class FeedEntry
{
    /**
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @var Craftsman|null
     */
    private $craftsman;

    /**
     * @var int|null
     */
    private $count;

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     */
    public function setTimestamp(\DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return Craftsman|null
     */
    public function getCraftsman(): ?Craftsman
    {
        return $this->craftsman;
    }

    /**
     * @param Craftsman|null $craftsman
     */
    public function setCraftsman(?Craftsman $craftsman): void
    {
        $this->craftsman = $craftsman;
    }

    /**
     * @return int|null
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * @param int|null $count
     */
    public function setCount(?int $count): void
    {
        $this->count = $count;
    }
}
