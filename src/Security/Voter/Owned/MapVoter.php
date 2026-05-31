<?php

namespace App\Security\Voter\Owned;

use App\Entity\ConstructionSite;
use App\Entity\Map;

/**
 * @implements AbstractConstructionSiteOwnedVoter<Map>
 */
class MapVoter extends AbstractConstructionSiteOwnedVoter
{
    public const string MAP_VIEW = 'MAP_VIEW';
    public const string MAP_MODIFY = 'MAP_MODIFY';

    protected function isInstanceOf(mixed $subject): bool
    {
        return $subject instanceof Map;
    }

    protected function getConstructionSite(mixed $subject): ?ConstructionSite
    {
        return $subject->getConstructionSite();
    }

    protected function getViewRole(): string
    {
        return self::MAP_VIEW;
    }

    protected function getModifyRole(): string
    {
        return self::MAP_MODIFY;
    }
}
