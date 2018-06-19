<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Response;


use App\Api\Entity\Building;
use App\Api\Entity\Craftsman;
use App\Api\Entity\Issue;
use App\Api\Entity\Map;
use App\Api\Entity\ObjectMeta;
use App\Api\Request\Base\AbstractRequest;
use App\Api\Request\Base\AuthenticatedRequest;
use App\Api\Response\Base\BaseResponse;
use App\Entity\Marker;

class ReadResponse extends BaseResponse
{
    /**
     * @var Craftsman[]
     */
    private $changedCraftsmen;

    /**
     * @var string[]
     */
    private $removedCraftsmanIds;

    /**
     * @var Building[]
     */
    private $changedBuildings;

    /**
     * @var string[]
     */
    private $removedBuildingIds;

    /**
     * @var Map[]
     */
    private $changedMaps;

    /**
     * @var string[]
     */
    private $removedMapIds;

    /**
     * @var Issue[]
     */
    private $changedIssues;

    /**
     * @var string[]
     */
    private $removedIssueIds;
}