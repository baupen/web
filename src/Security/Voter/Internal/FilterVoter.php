<?php

namespace App\Security\Voter\Internal;

use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Entity\Task;

/**
 * @extends AbstractConstructionSiteInternalVoter<Filter>
 */
class FilterVoter extends AbstractConstructionSiteInternalVoter
{
    public const string FILTER_CREATE = 'FILTER_CREATE';
    public const string FILTER_VIEW = 'FILTER_VIEW';

    protected function isInstanceOf(mixed $subject): bool
    {
        return $subject instanceof Filter;
    }

    protected function getConstructionSite(mixed $subject): ?ConstructionSite
    {
        return $subject->getConstructionSite();
    }

    protected function getRoles(): array
    {
        return [self::FILTER_CREATE, self::FILTER_VIEW];
    }
}
