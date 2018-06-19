<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Request;


use App\Api\Entity\Issue;
use App\Api\Entity\ObjectMeta;
use App\Api\Request\Base\AbstractRequest;
use App\Api\Request\Base\AuthenticatedRequest;

class IssueModify extends AuthenticatedRequest
{
    /**
     * @var Issue[]
     */
    private $issue;
}