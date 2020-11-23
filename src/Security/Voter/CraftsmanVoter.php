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

class CraftsmanVoter extends ConstructionSiteOwnedEntityVoter
{
    public const CRAFTSMAN_VIEW = 'CRAFTSMAN_VIEW';
    public const CRAFTSMAN_MODIFY = 'CRAFTSMAN_MODIFY';

    protected function isExpectedConstructionSiteOwnedEntityInstance(ConstructionSiteOwnedEntityInterface $constructionSiteOwnedEntity): bool
    {
        return $constructionSiteOwnedEntity instanceof Craftsman;
    }

    protected function getAttributes(): array
    {
        return [self::CRAFTSMAN_VIEW, self::CRAFTSMAN_MODIFY];
    }

    protected function getConstructionManagerAccessibleAttributes(ConstructionManager $manager): array
    {
        return [self::CRAFTSMAN_VIEW, self::CRAFTSMAN_MODIFY];
    }

    protected function getCraftsmanAccessibleAttributes(Craftsman $craftsman): array
    {
        return [self::CRAFTSMAN_VIEW];
    }

    protected function getFilterAccessibleAttributes(Filter $filter): array
    {
        return [self::CRAFTSMAN_VIEW];
    }

    /**
     * @param Craftsman $subject
     */
    protected function isIncludedInFilter(Filter $filter, $attribute, $subject): bool
    {
        return (null === $filter->getCraftsmanIds() || in_array($subject->getId(), $filter->getCraftsmanIds())) &&
            (null === $filter->getCraftsmanTrades() || in_array($subject->getTrade(), $filter->getCraftsmanTrades()));
    }
}
