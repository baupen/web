<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/24/18
 * Time: 6:11 PM
 */

namespace App\Model\BuildingMap;


use App\Entity\Map;
use App\Entity\Marker;
use App\Model\Base\MarkerInfo;

class BuildingMapMarkerInfo
{
    /**
     * @var Map
     */
    private $buildingMap;

    /**
     * @var Marker[]
     */
    private $marker = [];

    /**
     * @return Map
     */
    public function getBuildingMap(): Map
    {
        return $this->buildingMap;
    }

    /**
     * @param Map $buildingMap
     */
    public function setBuildingMap(Map $buildingMap): void
    {
        $this->buildingMap = $buildingMap;
    }

    /**
     * @param Marker $marker
     */
    public function addMarker(Marker $marker)
    {
        $this->marker[] = $marker;
    }

    /**
     * @return Marker[]
     */
    public function getMarkers(): array
    {
        return $this->marker;
    }
}