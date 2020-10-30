<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/*
 * automatically keeps track of creation time & last change time
 */

trait SoftDeleteTrait
{
    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    /**
     * @Groups({"construction-site-read", "craftsman-read"})
     */
    public function getIsDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    /**
     * never undelete an entity!
     */
    public function delete(): void
    {
        $this->deletedAt = new \DateTime();
    }
}
