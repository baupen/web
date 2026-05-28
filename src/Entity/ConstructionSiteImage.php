<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\FileTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class ConstructionSiteImage extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use FileTrait;

    #[ORM\ManyToOne(targetEntity: ConstructionSite::class)]
    private ?ConstructionSite $createdFor = null;

    public function getCreatedFor(): ConstructionSite
    {
        return $this->createdFor;
    }

    public function setCreatedFor(ConstructionSite $createdFor): void
    {
        $this->createdFor = $createdFor;
    }
}
