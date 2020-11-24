<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Interfaces;

use App\Entity\ConstructionSite;

interface ConstructionSiteOwnedEntityInterface
{
    public function isConstructionSiteSet(): bool;

    public function getConstructionSite(): ConstructionSite;
}
