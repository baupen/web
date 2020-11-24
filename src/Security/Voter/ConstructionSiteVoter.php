<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter;

use App\Entity\ConstructionSite;
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;

class ConstructionSiteVoter extends ConstructionSiteOwnedEntityVoter
{
    public const CONSTRUCTION_SITE_CREATE = 'CONSTRUCTION_SITE_CREATE';
    public const CONSTRUCTION_SITE_VIEW = 'CONSTRUCTION_SITE_VIEW';
    public const CONSTRUCTION_SITE_MODIFY = 'CONSTRUCTION_SITE_MODIFY';

    protected function isInstanceOf($entity): bool
    {
        return $entity instanceof ConstructionSite;
    }

    protected function getAllAttributes(): array
    {
        return [self::CONSTRUCTION_SITE_CREATE, self::CONSTRUCTION_SITE_VIEW, self::CONSTRUCTION_SITE_MODIFY];
    }

    protected function getReadOnlyAttributes(): array
    {
        return [self::CONSTRUCTION_SITE_VIEW];
    }

    protected function getConstructionManagerAttributes(): array
    {
        return [self::CONSTRUCTION_SITE_VIEW, self::CONSTRUCTION_SITE_MODIFY];
    }

    protected function getConstructionManagerAttributesWhichAreConstructionSiteIndependent(): array
    {
        return [self::CONSTRUCTION_SITE_CREATE, self::CONSTRUCTION_SITE_VIEW];
    }
}
