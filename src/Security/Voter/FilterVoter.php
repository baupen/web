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

use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;

class FilterVoter extends ConstructionSiteOwnedEntityVoter
{
    public const FILTER_CREATE = 'FILTER_CREATE';
    public const FILTER_VIEW = 'FILTER_VIEW';

    protected function isExpectedConstructionSiteOwnedEntityInstance(ConstructionSiteOwnedEntityInterface $constructionSiteOwnedEntity): bool
    {
        return $constructionSiteOwnedEntity instanceof Filter;
    }

    protected function getAttributes(): array
    {
        return [self::FILTER_CREATE, self::FILTER_VIEW];
    }

    protected function getConstructionManagerAccessibleAttributes(ConstructionManager $manager): array
    {
        return [self::FILTER_CREATE, self::FILTER_VIEW];
    }

    protected function getCraftsmanAccessibleAttributes(Craftsman $craftsman): array
    {
        return [];
    }

    protected function getFilterAccessibleAttributes(Filter $filter): array
    {
        return [self::FILTER_VIEW];
    }

    /**
     * @param Filter $subject
     */
    protected function isIncludedInFilter(Filter $filter, $attribute, $subject): bool
    {
        return self::FILTER_CREATE === $attribute || $filter->getId() === $subject->getId();
    }
}
