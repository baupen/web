<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity;

use App\Api\Entity\Base\BaseEntity;

class User extends BaseEntity
{
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

    /**
     * @return string
     */
    public function getAuthenticationToken(): string
    {
        return $this->authenticationToken;
    }

    /**
     * @param string $authenticationToken
     */
    public function setAuthenticationToken(string $authenticationToken): void
    {
        $this->authenticationToken = $authenticationToken;
    }

    /**
     * @return string
     */
    public function getGivenName(): string
    {
        return $this->givenName;
    }

    /**
     * @param string $givenName
     */
    public function setGivenName(string $givenName): void
    {
        $this->givenName = $givenName;
    }

    /**
     * @return string
     */
    public function getFamilyName(): string
    {
        return $this->familyName;
    }

    /**
     * @param string $familyName
     */
    public function setFamilyName(string $familyName): void
    {
        $this->familyName = $familyName;
    }
}
