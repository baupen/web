<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 10:37 AM
 */

namespace App\Api\Request;


use App\Api\Entity\Building;
use App\Api\Entity\Issue;
use App\Api\Entity\Map;
use App\Api\Request\Base\AbstractRequest;
use App\Api\Request\Base\AuthenticatedRequest;

class DownloadFileRequest extends AuthenticatedRequest
{
    /**
     * @var Building|null
     */
    private $building;

    /**
     * @var Map|null
     */
    private $map;

    /**
     * @var Issue
     */
    private $issue;
}