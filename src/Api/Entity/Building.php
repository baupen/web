<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;

use App\Api\Entity\Base\BaseEntity;

class Building extends BaseEntity
{
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Address|null
     */
    public function getAddress(): ?Address
    {
        return $this->address;
    }

    /**
     * @param Address|null $address
     */
    public function setAddress(?Address $address): void
    {
        $this->address = $address;
    }

    /**
     * @return null|string
     */
    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    /**
     * @param null|string $imageFilename
     */
    public function setImageFilename(?string $imageFilename): void
    {
        $this->imageFilename = $imageFilename;
    }

    /**
     * @return string[]
     */
    public function getMaps(): array
    {
        return $this->maps;
    }

    /**
     * @param string[] $maps
     */
    public function setMaps(array $maps): void
    {
        $this->maps = $maps;
    }

    /**
     * @return string[]
     */
    public function getCraftsmen(): array
    {
        return $this->craftsmen;
    }

    /**
     * @param string[] $craftsmen
     */
    public function setCraftsmen(array $craftsmen): void
    {
        $this->craftsmen = $craftsmen;
    }
}
