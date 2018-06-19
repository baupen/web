<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Response;


use App\Api\Response\Base\BaseResponse;
use App\Entity\ConstructionManager;
use App\Entity\Building;
use App\Entity\Map;
use App\Entity\Craftsman;
use App\Entity\Marker;

class SyncResponse extends BaseResponse
{
    /**
     * @var ConstructionManager $user
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
     * @var Map[]
     */
    private $buildingMaps;

    /**
     * @var Marker[]
     */
    private $markers;

    /**
     * @return ConstructionManager
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param ConstructionManager $user
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
     * @return Map[]
     */
    public function getBuildingMaps()
    {
        return $this->buildingMaps;
    }

    /**
     * @param Map[] $buildingMaps
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