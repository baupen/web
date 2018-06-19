<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;


class IssueStatusEvent
{
    use BaseEntity;

    /**
     * @var string
     */
    private $dateTime;

    /**
     * @var string
     */
    private $author;
}