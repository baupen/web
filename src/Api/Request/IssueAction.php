<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 11:00 AM
 */

namespace App\Api\Request;


use App\Api\Request\Base\AuthenticatedRequest;

class IssueAction extends AuthenticatedRequest
{
    /**
     * @var string
     */
    private $issueID;
}