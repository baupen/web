<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;


class Building
{
    use BaseEntity;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Address|null
     */
    private $address;

    /**
     * @var string|null
     */
    private $imageFilename;

    /**
     * @var string[]
     */
    private $maps;

    /**
     * @var string[]
     */
    private $craftsmen;
}