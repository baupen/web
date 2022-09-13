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

class IssueGroup
{
    /**
     * @var string
     *
     * @Groups({"issue-read"})
     */
    private $entity;

    /**
     * @var int
     *
     * @Groups({"issue-read"})
     */
    private $count;

    /**
     * @var \DateTime|null
     *
     * @Groups({"issue-read"})
     */
    private $earliestDeadline;

    public static function create(string $iri, int $count, ?\DateTime $earliestDeadline)
    {
        $self = new self();

        $self->entity = $iri;
        $self->count = $count;
        $self->earliestDeadline = $earliestDeadline;

        return $self;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getEarliestDeadline(): ?\DateTime
    {
        return $this->earliestDeadline;
    }
}
