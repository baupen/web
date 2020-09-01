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
use App\Service\Upload\Frame;
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
     * @var IssuePosition[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\IssuePosition", mappedBy="mapFile")
     */
    private $issuePositions;

    /**
     * @var MapSector[]
     *
     * @ORM\OneToMany(targetEntity="MapSector", mappedBy="mapFile")
     */
    private $sectors;

    /**
     * @var Frame|null
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $sectorFrame;

    public function __construct()
    {
        $this->issuePositions = new ArrayCollection();
        $this->sectors = new ArrayCollection();
    }

    /**
     * @return IssuePosition[]|ArrayCollection
     */
    public function getIssuePositions()
    {
        return $this->issuePositions;
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
     * @return Frame|object|null
     */
    public function getSectorFrame()
    {
        if (\is_object($this->sectorFrame) || null === $this->sectorFrame) {
            return $this->sectorFrame;
        }

        // doctrine deserializes to associative array instead of object
        return (object) $this->sectorFrame;
    }

    public function setSectorFrame(?Frame $sectorFrame): void
    {
        $this->sectorFrame = $sectorFrame;
    }

    /**
     * @return MapSector[]|ArrayCollection
     */
    public function getSectors()
    {
        return $this->sectors;
    }
}
