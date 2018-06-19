<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;


class IssueStatus
{
    use BaseEntity;

    /**
     * @var IssueStatusEvent|null
     */
    private $creation;

    /**
     * @var IssueStatusEvent|null
     */
    private $response;

    /**
     * @var IssueStatusEvent|null
     */
    private $review;
}