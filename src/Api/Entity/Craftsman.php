<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;


class Craftsman
{
    use BaseEntity;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $trade;
}