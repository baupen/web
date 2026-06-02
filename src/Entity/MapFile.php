<?php

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
