<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Data\Response;


use App\Api\Entity\Building;
use App\Api\Entity\Craftsman;
use App\Api\Entity\Issue;
use App\Api\Entity\Map;
use App\Api\Entity\ObjectMeta;
use App\Api\Request\Base\AbstractRequest;
use App\Api\Request\Base\AuthenticatedRequest;
use App\Api\Response\Base\AbstractResponse;

class ReadData extends AbstractResponse
{
    /**
     * @var Craftsman[]
     */
    private $changedCraftsmen;

    /**
     * @var string[]
     */
    private $removedCraftsmanIDs;

    /**
     * @var Building[]
     */
    private $changedBuildings;

    /**
     * @var string[]
     */
    private $removedBuildingIDs;

    /**
     * @var Map[]
     */
    private $changedMaps;

    /**
     * @var string[]
     */
    private $removedMapIDs;

    /**
     * @var Issue[]
     */
    private $changedIssues;

    /**
     * @var string[]
     */
    private $removedIssueIDs;
}