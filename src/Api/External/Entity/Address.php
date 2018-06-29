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

    /**
     * @return null|string
     */
    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }

    /**
     * @param null|string $streetAddress
     */
    public function setStreetAddress(?string $streetAddress): void
    {
        $this->streetAddress = $streetAddress;
    }

    /**
     * @return int|null
     */
    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    /**
     * @param int|null $postalCode
     */
    public function setPostalCode(?int $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return null|string
     */
    public function getLocality(): ?string
    {
        return $this->locality;
    }

    /**
     * @param null|string $locality
     */
    public function setLocality(?string $locality): void
    {
        $this->locality = $locality;
    }

    /**
     * @return null|string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param null|string $country
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }
}
