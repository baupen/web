<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Switch_;

use DateTime;

class ConstructionSite extends \App\Api\Entity\Base\ConstructionSite
{
    /**
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var string|null
     */
    private $imageMedium;

    /**
     * @var string[]
     */
    private $address;

    /**
     * @var string[]
     */
    private $otherConstructionManagers;

    /**
     * @var bool
     */
    private $isConstructionManagerOf;

    /**
     * @var string|null
     */
    private $switchLink;

    /**
     * @return string|null
     */
    public function getImageMedium(): ?string
    {
        return $this->imageMedium;
    }

    /**
     * @param string|null $imageMedium
     */
    public function setImageMedium(?string $imageMedium): void
    {
        $this->imageMedium = $imageMedium;
    }

    /**
     * @return bool
     */
    public function getIsConstructionManagerOf(): bool
    {
        return $this->isConstructionManagerOf;
    }

    /**
     * @param bool $isConstructionManagerOf
     */
    public function setIsConstructionManagerOf(bool $isConstructionManagerOf): void
    {
        $this->isConstructionManagerOf = $isConstructionManagerOf;
    }

    /**
     * @return string[]
     */
    public function getAddress(): array
    {
        return $this->address;
    }

    /**
     * @param string[] $address
     */
    public function setAddress(array $address): void
    {
        $this->address = $address;
    }

    /**
     * @return string[]
     */
    public function getOtherConstructionManagers(): array
    {
        return $this->otherConstructionManagers;
    }

    /**
     * @param string[] $constructionSiteManagers
     */
    public function setOtherConstructionManagers(array $constructionSiteManagers): void
    {
        $this->otherConstructionManagers = $constructionSiteManagers;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string|null
     */
    public function getSwitchLink(): ?string
    {
        return $this->switchLink;
    }

    /**
     * @param string|null $switchLink
     */
    public function setSwitchLink(?string $switchLink): void
    {
        $this->switchLink = $switchLink;
    }
}
