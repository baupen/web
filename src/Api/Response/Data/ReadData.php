<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Response\Data;

use App\Api\Entity\Building;
use App\Api\Entity\Craftsman;
use App\Api\Entity\Issue;
use App\Api\Entity\Map;
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