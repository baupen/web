<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

class FeedEntry
{
    public const TYPE_CONSTRUCTION_MANAGER_REGISTERED = 1;
    public const TYPE_CRAFTSMAN_RESOLVED = 2;
    public const TYPE_CONSTRUCTION_MANAGER_CLOSED = 3;

    public const TYPE_CRAFTSMAN_VISITED_WEBPAGE = 10;

    /**
     * @var string
     *
     * @Groups({"issue-read","craftsman-read"})
     */
    private $date;

    /**
     * @var string
     *
     * @Groups({"issue-read","craftsman-read"})
     */
    private $subject;

    /**
     * @var int
     *
     * @Groups({"issue-read","craftsman-read"})
     */
    private $type;

    /**
     * @var int
     *
     * @Groups({"issue-read","craftsman-read"})
     */
    private $count;

    /**
     * FeedEntry constructor.
     */
    public function __construct(string $date, string $subjectId, int $type, int $count)
    {
        $this->date = $date;
        $this->subject = $subjectId;
        $this->type = $type;
        $this->count = $count;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
