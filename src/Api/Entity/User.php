<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;


class User
{
    use BaseEntity;

    /**
     * @var string
     */
    private $authenticationToken;

    /**
     * @var string
     */
    private $givenName;

    /**
     * @var string
     */
    private $familyName;
}