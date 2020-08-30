<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Edit;

class ConstructionSite extends \App\Api\Entity\Base\ConstructionSite
{
    /**
     * @var string
     */
    private $streetAddress;

    /**
     * @var int
     */
    private $postalCode;

    /**
     * @var string
     */
    private $locality;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string|null
     */
    private $imageMedium;

    public function getStreetAddress(): string
    {
        return $this->streetAddress;
    }

    public function setStreetAddress(string $streetAddress): void
    {
        $this->streetAddress = $streetAddress;
    }

    public function getPostalCode(): int
    {
        return $this->postalCode;
    }

    public function setPostalCode(int $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getLocality(): string
    {
        return $this->locality;
    }

    public function setLocality(string $locality): void
    {
        $this->locality = $locality;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getImageMedium(): ?string
    {
        return $this->imageMedium;
    }

    public function setImageMedium(?string $imageMedium): void
    {
        $this->imageMedium = $imageMedium;
    }
}
