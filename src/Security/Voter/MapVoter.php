<?php

namespace App\Security\Voter;

use App\Entity\Filter;
use App\Entity\Map;
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;

class MapVoter extends ConstructionSiteOwnedEntityVoter
{
    public const MAP_VIEW = 'MAP_VIEW';
    public const MAP_MODIFY = 'MAP_MODIFY';

    protected function isInstanceOf($entity): bool
    {
        return $entity instanceof Map;
    }

    protected function getAllAttributes(): array
    {
        return [self::MAP_VIEW, self::MAP_MODIFY];
    }

    protected function getReadOnlyAttributes(): array
    {
        return [self::MAP_VIEW];
    }

    protected function isIncludedInFilter(Filter $filter, $attribute, $subject): bool
    {
        if (null === $filter->getMapIds()) {
            return true;
        }

        return in_array($subject->getId(), $filter->getMapIds());
    }
}
