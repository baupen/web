<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;


class Address
{
    /**
     * @var string|null
     */
    private $streetAddress;

    /**
     * @var int|null
     */
    private $postalCode;

    /**
     * @var string|null
     */
    private $locality;

    /**
     * @var string|null
     */
    private $country;

}