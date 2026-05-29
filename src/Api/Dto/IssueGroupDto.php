<?php

namespace App\Api\Dto;

use Symfony\Component\Serializer\Attribute\Groups;

class IssueGroupDto
{
    #[Groups(['issue-read'])]
    private ?string $entity = null;

    #[Groups(['issue-read'])]
    private ?int $count = null;

    #[Groups(['issue-read'])]
    private ?\DateTime $earliestDeadline = null;

    public static function create(string $entity, int $count, ?\DateTime $earliestDeadline): self
    {
        $self = new self();

        $self->entity = $entity;
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
