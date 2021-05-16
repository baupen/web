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
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class ConstructionSiteImage extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use FileTrait;

    /**
     * @var ConstructionSite
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionSite")
     */
    private $createdFor;

    public function getCreatedFor(): ConstructionSite
    {
        return $this->createdFor;
    }

    public function setCreatedFor(ConstructionSite $createdFor): void
    {
        $this->createdFor = $createdFor;
    }
}
