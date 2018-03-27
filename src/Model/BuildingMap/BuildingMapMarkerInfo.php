<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/24/18
 * Time: 6:11 PM
 */

namespace App\Model\BuildingMap;


use App\Entity\BuildingMap;
use App\Entity\Craftsman;
use App\Model\Base\MarkerInfo;

class BuildingMapMarkerInfo extends MarkerInfo
{
    /**
     * @var BuildingMap
     */
    private $buildingMap;

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
}