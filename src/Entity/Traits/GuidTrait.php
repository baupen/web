<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/*
 * the id used in the entities
 */

trait GuidTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $guid;

    /**
     * @return int
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersistGuid()
    {
        $this->guid = Uuid::uuid4();
    }
}
