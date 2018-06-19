<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;


class Issue
{
    use BaseEntity;

    /**
     * @var int|null
     */
    private $number;

    /**
     * @var bool
     */
    private $isMarked;

    /**
     * @var bool
     */
    private $wasAddedWithClient;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $craftsman;

    /**
     * @var string|null
     */
    private $imageFileName;

    /**
     * @var IssueStatus
     */
    private $status;

    /**
     * @var IssuePosition
     */
    private $position;
}