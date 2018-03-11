<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Request;


use App\Api\ApiSerializable;
use App\Api\Request\Base\BaseRequest;
use App\Api\Response\Base\BaseResponse;
use App\Entity\AppUser;
use App\Entity\Building;
use App\Entity\BuildingMap;
use App\Entity\Craftsman;
use App\Entity\Marker;
use AppTestBundle\Entity\FunctionalTests\User;
use Symfony\Component\Validator\Constraints as Assert;

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