<?php

namespace App\Entity\Interfaces;

use App\Entity\ConstructionSite;

interface ConstructionSiteOwnedEntityInterface
{
    public function isConstructionSiteSet(): bool;

    public function getConstructionSite(): ConstructionSite;
}
