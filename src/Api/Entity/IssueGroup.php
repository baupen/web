<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity;

class IssueGroup
{
    /**
     * @var string
     */
    private $entity;

    /**
     * @var int
     */
    private $count;

    /**
     * @var \DateTime|null
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
