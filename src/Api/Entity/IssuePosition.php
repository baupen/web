<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;


class IssuePosition
{
    use BaseEntity;

    /**
     * @var double
     */
    private $x;

    /**
     * @var double
     */
    private $y;

    /**
     * @var double
     */
    private $zoomScale;
}