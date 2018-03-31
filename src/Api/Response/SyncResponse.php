<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Response;


use App\Api\Response\Base\BaseResponse;
use App\Entity\AppUser;
use App\Entity\Building;
use App\Entity\BuildingMap;
use App\Entity\Craftsman;
use App\Entity\Marker;

class SyncResponse extends BaseResponse
{
    /**
     * @var AppUser $user
     */
    private $user;

    /**
     * @var Craftsman[]
     */
    private $craftsmen;

    /**
     * @var Building[]
     */
    private $buildings;

    /**
     * @var BuildingMap[]
     */
    private $buildingMaps;

    /**
     * @var Marker[]
     */
    private $markers;

    /**
     * @return AppUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param AppUser $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return Craftsman[]
     */
    public function getCraftsmen()
    {
        return $this->craftsmen;
    }

    /**
     * @param Craftsman[] $craftsmen
     */
    public function setCraftsmen(array $craftsmen): void
    {
        $this->craftsmen = $craftsmen;
    }

    /**
     * @return Building[]
     */
    public function getBuildings()
    {
        return $this->buildings;
    }

    /**
     * @param Building[] $buildings
     */
    public function setBuildings(array $buildings): void
    {
        $this->buildings = $buildings;
    }

    /**
     * @return BuildingMap[]
     */
    public function getBuildingMaps()
    {
        return $this->buildingMaps;
    }

    /**
     * @param BuildingMap[] $buildingMaps
     */
    public function setBuildingMaps(array $buildingMaps): void
    {
        $this->buildingMaps = $buildingMaps;
    }

    /**
     * @return Marker[]
     */
    public function getMarkers()
    {
        return $this->markers;
    }

    /**
     * @param Marker[] $markers
     */
    public function setMarkers(array $markers): void
    {
        $this->markers = $markers;
    }

    /**
     *
     */
    public function prepareSerialization()
    {
        parent::prepareSerialization();
        foreach ($this->craftsmen as $craftsman) {
            $craftsman->flattenDoctrineStructures();
        }
        foreach ($this->markers as $marker) {
            $marker->flattenDoctrineStructures();
        }
        foreach ($this->buildings as $building) {
            $building->flattenDoctrineStructures();
        }
        foreach ($this->buildingMaps as $buildingMap) {
            $buildingMap->flattenDoctrineStructures();
        }
        $this->user->flattenDoctrineStructures();
    }
}