<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/*
 * Address information
 */

trait AddressTrait
{
    #[Groups(['address:read', 'address:write'])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $streetAddress = null;

    #[Groups(['address:read', 'address:write'])]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $postalCode = null;

    #[Groups(['address:read', 'address:write'])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $locality = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Country]
    private ?string $country = 'CH';

    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }

    public function setStreetAddress(string $streetAddress): void
    {
        $this->streetAddress = $streetAddress;
    }

    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function setPostalCode(int $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function setLocality(string $locality): void
    {
        $this->locality = $locality;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * returns all non-empty address lines.
     *
     * @return string[]
     */
    public function getAddressLines(): array
    {
        $res = explode("\n", $this->getStreetAddress());
        $prefix = '';
        if ($this->getCountry()) {
            $prefix = $this->getCountry() . ($this->getPostalCode() ? '-' : ' ');
        }
        if ($this->getPostalCode()) {
            $prefix .= $this->getPostalCode() . ' ';
        }
        if ($this->getLocality()) {
            $prefix .= $this->getLocality();
        }
        $res[] = trim($prefix);

        return array_filter($res);
    }
}
