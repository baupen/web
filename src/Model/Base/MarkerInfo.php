<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/24/18
 * Time: 6:11 PM
 */

namespace App\Model\Base;


use App\Entity\BuildingMap;
use App\Entity\Craftsman;

class MarkerInfo
{
    /**
     * @var integer
     */
    private $openMarkers = 0;

    /**
     * @var integer
     */
    private $closedMarkers = 0;

    /**
     * @return int
     */
    public function getOpenMarkers(): int
    {
        return $this->openMarkers;
    }

    /**
     * @param int $openMarkers
     */
    public function setOpenMarkers(int $openMarkers): void
    {
        $this->openMarkers = $openMarkers;
    }

    /**
     * @return int
     */
    public function getClosedMarkers(): int
    {
        return $this->closedMarkers;
    }

    /**
     * @param int $closedMarkers
     */
    public function setClosedMarkers(int $closedMarkers): void
    {
        $this->closedMarkers = $closedMarkers;
    }
}