<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/*
 * Address information
 */

trait AddressTrait
{
    #[Assert\NotBlank]
    #[Groups(['construction-site-read', 'construction-site-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    private ?string $streetAddress = null;

    #[Assert\NotBlank]
    #[Groups(['construction-site-read', 'construction-site-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $postalCode = null;

    #[Assert\NotBlank]
    #[Groups(['construction-site-read', 'construction-site-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    private ?string $locality = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    #[Assert\Country]
    private ?string $country = 'CH';

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

    /**
     * returns all non-empty address lines.
     *
     * @return string[]
     */
    public function getAddressLines(): array
    {
        $res = explode("\n", $this->getStreetAddress());
        $prefix = '';
        if (mb_strlen($this->getCountry()) > 0) {
            $prefix = $this->getCountry().((mb_strlen($this->getPostalCode()) > 0) ? '-' : ' ');
        }
        if (mb_strlen($this->getPostalCode()) > 0) {
            $prefix .= $this->getPostalCode().' ';
        }
        if (mb_strlen($this->getLocality()) > 0) {
            $prefix .= $this->getLocality();
        }
        $res[] = trim($prefix);

        $result = [];
        foreach ($res as $entry) {
            if (mb_strlen($entry) > 0) {
                $result[] = $entry;
            }
        }

        return $result;
    }
}
