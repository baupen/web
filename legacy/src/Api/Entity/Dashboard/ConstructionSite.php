<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Dashboard;

class ConstructionSite extends \App\Api\Entity\Base\ConstructionSite
{
    /**
     * @var string|null
     */
    private $imageMedium;

    public function getImageMedium(): ?string
    {
        return $this->imageMedium;
    }

    public function setImageMedium(?string $imageFull): void
    {
        $this->imageMedium = $imageFull;
    }
}
