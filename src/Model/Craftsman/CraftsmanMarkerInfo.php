<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/24/18
 * Time: 6:11 PM
 */

namespace App\Model\Craftsman;


use App\Entity\Craftsman;
use App\Model\Base\MarkerInfo;

class CraftsmanMarkerInfo extends MarkerInfo
{
    /**
     * @var Craftsman
     */
    private $craftsman;

    /**
     * @return Craftsman
     */
    public function getCraftsman(): Craftsman
    {
        return $this->craftsman;
    }

    /**
     * @param Craftsman $craftsman
     */
    public function setCraftsman(Craftsman $craftsman): void
    {
        $this->craftsman = $craftsman;
    }
}