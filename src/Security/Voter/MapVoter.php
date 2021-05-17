<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        return null === $filter->getMapIds() || in_array($subject->getId(), $filter->getMapIds());
    }
}
