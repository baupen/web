<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\GuidTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\ThingTrait;
use App\Enum\EmailType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Email is a sent email to the specified receivers.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\BuildingMapRepository")
 * @ORM\HasLifecycleCallbacks
 */
class BuildingMap extends BaseEntity
{
    use IdTrait;
    use GuidTrait;
    use ThingTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $fileName;

    /**
     * @var Building
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Building", inversedBy="buildingMaps")
     */
    private $building;

    /**
     * @var Marker[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Marker", mappedBy="buildingMap")
     */
    private $markers;

    public function __construct()
    {
        $this->markers = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return Building
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param Building $building
     */
    public function setBuilding($building)
    {
        $this->building = $building;
    }

    /**
     * @return Marker[]|ArrayCollection
     */
    public function getMarkers()
    {
        return $this->markers;
    }
}
