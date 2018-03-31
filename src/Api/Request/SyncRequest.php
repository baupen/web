<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Request;


use App\Api\Request\Base\BaseRequest;
use App\Entity\Marker;

class SyncRequest extends BaseRequest
{
    /**
     * @var Marker[]
     */
    private $markers;

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
}