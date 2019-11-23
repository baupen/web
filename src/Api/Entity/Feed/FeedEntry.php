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

use App\Api\Entity\Base\Craftsman;
use DateTime;

class FeedEntry
{
    const RESPONSE_RECEIVED = 'response-received';
    const VISITED_WEBPAGE = 'visited-webpage';
    const OVERDUE = 'overdue';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int|null
     */
    private $count;

    /**
     * @var Craftsman
     */
    private $craftsman;

    /**
     * @var DateTime
     */
    private $timestamp;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(?int $count): void
    {
        $this->count = $count;
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getCraftsman(): Craftsman
    {
        return $this->craftsman;
    }

    public function setCraftsman(Craftsman $craftsman): void
    {
        $this->craftsman = $craftsman;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
