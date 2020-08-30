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

class ConstructionSite extends BaseEntity
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
     * @var File|null
     */
    private $image;

    /**
     * @var string[]
     */
    private $maps;

    /**
     * @var string[]
     */
    private $craftsmen;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): void
    {
        $this->address = $address;
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

    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(?File $image): void
    {
        $this->image = $image;
    }
}
