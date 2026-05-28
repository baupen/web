<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/*
 * automatically keeps track of creation time & last change time
 */

trait SoftDeleteTrait
{
    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    /**
     * never undelete an entity!
     */
    public function delete(): void
    {
        $this->deletedAt = new \DateTime();
    }
}
