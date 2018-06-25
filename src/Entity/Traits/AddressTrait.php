<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/*
 * Address information
 */

trait AddressTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $streetAddress;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $locality;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Country()
     */
    private $country = 'CH';

    /**
     * Get street.
     *
     * @return string
     */
    public function getStreetAddress()
    {
        return $this->streetAddress;
    }

    /**
     * Set street.
     *
     * @param string $streetAddress
     *
     * @return static
     */
    public function setStreetAddress($streetAddress)
    {
        $this->streetAddress = $streetAddress;

        return $this;
    }

    /**
     * Get postalCode.
     *
     * @return int
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set postalCode.
     *
     * @param int $postalCode
     *
     * @return static
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get addressRegion.
     *
     * @return string
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * Set addressRegion.
     *
     * @param string $locality
     *
     * @return static
     */
    public function setLocality($locality)
    {
        $this->locality = $locality;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return static
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * returns all non-empty address lines.
     *
     * @return string[]
     */
    public function getAddressLines()
    {
        $res = explode("\n", $this->getStreetAddress());
        $prefix = '';
        if (mb_strlen($this->getCountry()) > 0) {
            $prefix = $this->getCountry().' ';
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
