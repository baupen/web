<?php

namespace App\Security\Voter\Owned;

use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Map;

/**
 * @extends AbstractConstructionSiteOwnedVoter<Craftsman>
 */
class CraftsmanVoter extends AbstractConstructionSiteOwnedVoter
{
    public const string CRAFTSMAN_VIEW = 'CRAFTSMAN_VIEW';
    public const string CRAFTSMAN_MODIFY = 'CRAFTSMAN_MODIFY';

    protected function isInstanceOf(mixed $subject): bool
    {
        return $subject instanceof Craftsman;
    }

    protected function getConstructionSite(mixed $subject): ?ConstructionSite
    {
        return $subject->getConstructionSite();
    }

    protected function getViewRole(): string
    {
        return self::CRAFTSMAN_VIEW;
    }

    protected function getModifyRole(): string
    {
        return self::CRAFTSMAN_MODIFY;
    }
}
