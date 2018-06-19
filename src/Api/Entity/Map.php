<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;


class Map
{
    use BaseEntity;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $fileName;

    /**
     * @var string[]
     */
    private $children;

    /**
     * @var string[]
     */
    private $issues;
}