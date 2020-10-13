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
 * A MapFile is the actual .pdf file connected to a logical map.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class MapFile extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use FileTrait;

    /**
     * @var ConstructionSite
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionSite", inversedBy="mapFiles")
     */
    private $constructionSite;

    /**
     * @var Map|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Map", inversedBy="files")
     */
    private $map;

    /**
     * @var Issue[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Issue", mappedBy="mapFile")
     */
    private $issues;

    public function __construct()
    {
        $this->issues = new ArrayCollection();
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    public function getMap(): ?Map
    {
        return $this->map;
    }

    public function setMap(?Map $map): void
    {
        $this->map = $map;
    }

    /**
     * @return Issue[]|ArrayCollection
     */
    public function getIssues()
    {
        return $this->issues;
    }
}
