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
use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\PublicAccessibleTrait;
use App\Entity\Traits\ThingTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Email is a sent email to the specified receivers.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\BuildingRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Building extends BaseEntity implements ApiSerializable
{
    use IdTrait;
    use ThingTrait;
    use AddressTrait;
    use PublicAccessibleTrait;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isArchived = false;

    /**
     * @var AppUser[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\AppUser", mappedBy="buildings")
     */
    private $appUsers;

    /**
     * @var BuildingMap[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\BuildingMap", mappedBy="building")
     * @ORM\OrderBy({"name": "ASC"})
     */
    private $buildingMaps;

    /**
     * Building constructor.
     */
    public function __construct()
    {
        $this->appUsers = new ArrayCollection();
    }

    /**
     * @return AppUser[]|ArrayCollection
     */
    public function getAppUsers()
    {
        return $this->appUsers;
    }

    /**
     * @return BuildingMap[]|ArrayCollection
     */
    public function getBuildingMaps()
    {
        return $this->buildingMaps;
    }

    /**
     * @return Marker[]
     */
    public function getMarkers()
    {
        $markers = [];
        if ($this->getBuildingMaps() != null) {
            foreach ($this->getBuildingMaps() as $buildingMap) {
                $markers = array_merge($markers, $buildingMap->getMarkers()->toArray());
            }
        }
        return $markers;
    }

    /**
     * @return Marker[]
     */
    public function getOpenMarkerByCraftsman()
    {
        $markers = [];
        if ($this->getBuildingMaps() != null) {
            foreach ($this->getBuildingMaps() as $buildingMap) {
                $markers = array_merge($markers, $buildingMap->getMarkers()->toArray());
            }
        }
        return $markers;
    }

    /**
     * remove all array collections, setting them to null
     */
    public function flattenDoctrineStructures()
    {
        $this->appUsers = null;
        $this->buildingMaps = null;
    }

    /**
     * @param AppUser[]|ArrayCollection $appUsers
     */
    public function setAppUsers($appUsers): void
    {
        $this->appUsers = $appUsers;
    }

    /**
     * @return bool
     */
    public function isArchived()
    {
        return $this->isArchived;
    }

    /**
     * @param bool $isArchived
     */
    public function setIsArchived(bool $isArchived): void
    {
        $this->isArchived = $isArchived;
    }
}
