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


use App\Api\ApiSerializable;
use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\PublicAccessibleTrait;
use App\Entity\Traits\ThingTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * An Email is a sent email to the specified receivers.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\BuildingMapRepository")
 * @ORM\HasLifecycleCallbacks
 */
class BuildingMap extends BaseEntity implements ApiSerializable
{
    use IdTrait;
    use ThingTrait;
    use PublicAccessibleTrait;

    /**
     * @var UploadedFile
     */
    private $file;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
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

    /**
     * remove all array collections, setting them to null
     */
    public function flattenDoctrineStructures()
    {
        $this->markers = null;
        $this->building = $this->building->getId();
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file): void
    {
        $this->file = $file;
    }

    /**
     * @return int
     */
    public function newMarkerCount()
    {
        $count = 0;
        foreach ($this->getMarkers() as $marker) {
            if (!$marker->getViewedOnline()) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * @return int
     */
    public function openMarkerCount()
    {
        $count = 0;
        foreach ($this->getMarkers() as $marker) {
            if (!$marker->getApproved()) {
                $count++;
            }
        }
        return $count;
    }
}
