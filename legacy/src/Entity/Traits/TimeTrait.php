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
use Exception;

/*
 * automatically keeps track of creation time & last change time
 */

trait TimeTrait
{
    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $lastChangedAt;

    /**
     * @ORM\PrePersist()
     *
     * @throws Exception
     * @throws Exception
     */
    public function prePersistTime()
    {
        $this->createdAt = new DateTime();
        $this->lastChangedAt = new DateTime();
    }

    /**
     * @ORM\PreUpdate()
     *
     * @throws Exception
     */
    public function preUpdateTime()
    {
        $this->lastChangedAt = new DateTime();
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return DateTime
     */
    public function getLastChangedAt()
    {
        return $this->lastChangedAt;
    }
}
