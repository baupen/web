<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/*
 * automatically keeps track of creation time & last change time
 */

trait TimeTrait
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['time:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['time:read'])]
    private ?\DateTimeImmutable $lastChangedAt = null;

    #[ORM\PrePersist]
    public function prePersistTime(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->lastChangedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function preUpdateTime(): void
    {
        $this->lastChangedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastChangedAt(): \DateTimeImmutable
    {
        return $this->lastChangedAt;
    }
}
