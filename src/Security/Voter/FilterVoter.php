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
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;

class FilterVoter extends ConstructionSiteOwnedEntityVoter
{
    public const FILTER_CREATE = 'FILTER_CREATE';
    public const FILTER_VIEW = 'FILTER_VIEW';

    protected function isInstanceOf($entity): bool
    {
        return $entity instanceof Filter;
    }

    protected function getAllAttributes(): array
    {
        return [self::FILTER_CREATE, self::FILTER_VIEW];
    }

    protected function getReadOnlyAttributes(): array
    {
        return [self::FILTER_VIEW];
    }

    protected function getRelatedCraftsmanAccessibleAttributes(): array
    {
        return [];
    }

    /**
     * @param Filter $subject
     */
    protected function isIncludedInFilter(Filter $filter, $attribute, $subject): bool
    {
        return self::FILTER_CREATE === $attribute || $filter->getId() === $subject->getId();
    }
}
