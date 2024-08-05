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
    #[Groups(['issue-read'])]
    private ?string $entity = null;

    #[Groups(['issue-read'])]
    private ?int $count = null;

    #[Groups(['issue-read'])]
    private ?\DateTime $earliestDeadline = null;

    public static function create(string $iri, int $count, ?\DateTime $earliestDeadline): self
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
