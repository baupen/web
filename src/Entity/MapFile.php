<?php

/*
 * This file is part of the mangel.io project.
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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class MapFile extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use FileTrait;

    /**
     * @var Map
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Map", inversedBy="files")
     */
    private $map;

    /**
     * @var IssuePosition[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\IssuePosition", mappedBy="mapFile")
     */
    private $issuePositions;

    public function __construct()
    {
        $this->issuePositions = new ArrayCollection();
    }

    /**
     * @return Map
     */
    public function getMap(): Map
    {
        return $this->map;
    }

    /**
     * @param Map $map
     */
    public function setMap(Map $map): void
    {
        $this->map = $map;
    }

    /**
     * @return IssuePosition[]
     */
    public function getIssuePositions(): array
    {
        return $this->issuePositions;
    }
}
