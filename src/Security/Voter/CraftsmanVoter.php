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

use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;

class CraftsmanVoter extends ConstructionSiteOwnedEntityVoter
{
    public const CRAFTSMAN_VIEW = 'CRAFTSMAN_VIEW';
    public const CRAFTSMAN_MODIFY = 'CRAFTSMAN_MODIFY';

    protected function isInstanceOf($entity): bool
    {
        return $entity instanceof Craftsman;
    }

    protected function getAllAttributes(): array
    {
        return [self::CRAFTSMAN_VIEW, self::CRAFTSMAN_MODIFY];
    }

    protected function getReadOnlyAttributes(): array
    {
        return [self::CRAFTSMAN_VIEW];
    }

    /**
     * @param Craftsman $subject
     */
    protected function isIncludedInFilter(Filter $filter, $attribute, $subject): bool
    {
        return null === $filter->getCraftsmanIds() || in_array($subject->getId(), $filter->getCraftsmanIds());
    }
}
