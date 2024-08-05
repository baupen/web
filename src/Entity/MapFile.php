<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\FileTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * A MapFile is the actual .pdf file connected to a logical map.
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class MapFile extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use FileTrait;

    #[ORM\ManyToOne(targetEntity: Map::class)]
    private Map $createdFor;

    public function getCreatedFor(): Map
    {
        return $this->createdFor;
    }

    public function setCreatedFor(Map $createdFor): void
    {
        $this->createdFor = $createdFor;
    }
}
