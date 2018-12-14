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

class ConstructionSite extends \App\Api\Entity\Base\ConstructionSite
{
    /**
     * @var string|null
     */
    private $imageMedium;

    /**
     * @var bool
     */
    private $isConstructionManagerOf;

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
}
