<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Entity;

use App\Api\External\Entity\Base\BaseEntity;

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

    public function getAuthenticationToken(): string
    {
        return $this->authenticationToken;
    }

    public function setAuthenticationToken(string $authenticationToken): void
    {
        $this->authenticationToken = $authenticationToken;
    }

    public function getGivenName(): string
    {
        return $this->givenName;
    }

    public function setGivenName(string $givenName): void
    {
        $this->givenName = $givenName;
    }

    public function getFamilyName(): string
    {
        return $this->familyName;
    }

    public function setFamilyName(string $familyName): void
    {
        $this->familyName = $familyName;
    }
}
