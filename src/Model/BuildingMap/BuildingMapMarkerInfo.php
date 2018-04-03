<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/24/18
 * Time: 6:11 PM
 */

namespace App\Model\BuildingMap;


use App\Entity\BuildingMap;
use App\Entity\Marker;
use App\Model\Base\MarkerInfo;

class BuildingMapMarkerInfo
{
    /**
     * @var BuildingMap
     */
    private $buildingMap;

    /**
     * @var Marker[]
     */
    private $marker = [];

    /**
     * @return BuildingMap
     */
    public function getBuildingMap(): BuildingMap
    {
        return $this->buildingMap;
    }

    /**
     * @param BuildingMap $buildingMap
     */
    public function setBuildingMap(BuildingMap $buildingMap): void
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