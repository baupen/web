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
    private $maxDeadline;

    public static function create(string $iri, int $count, ?\DateTime $maxDeadline)
    {
        $self = new self();

        $self->entity = $iri;
        $self->count = $count;
        $self->maxDeadline = $maxDeadline;

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

    public function getMaxDeadline(): ?\DateTime
    {
        return $this->maxDeadline;
    }
}
