<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/*
 * automatically keeps track of creation time & last change time
 */

trait SoftDeleteTrait
{
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /**
     * never undelete an entity!
     */
    public function markAsDeleted(): void
    {
        $this->deletedAt = new \DateTimeImmutable();
    }

    #[Groups(['soft-delete:read'])]
    public function getIsDeleted(): bool
    {
        return null !== $this->deletedAt;
    }
}
