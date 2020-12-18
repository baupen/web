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
use App\Security\Voter\Base\ConstructionSiteRelatedEntityVoter;

class ConstructionManagerVoter extends ConstructionSiteRelatedEntityVoter
{
    public const CONSTRUCTION_MANAGER_VIEW = 'CONSTRUCTION_MANAGER_VIEW';

    /**
     * @param ConstructionManager $subject
     */
    protected function isConstructionManagerRelated(ConstructionManager $constructionManager, $subject)
    {
        foreach ($constructionManager->getConstructionSites() as $constructionSite) {
            if ($constructionSite->getConstructionManagers()->contains($subject)) {
                return true;
            }
        }

        return false;
    }

    protected function isCraftsmanRelated(Craftsman $craftsman, $subject)
    {
        return $craftsman->getConstructionSite()->getConstructionManagers()->contains($subject);
    }

    protected function isFilterRelated(Filter $filter, $subject)
    {
        return $filter->getConstructionSite()->getConstructionManagers()->contains($subject);
    }

    protected function isInstanceOf($entity): bool
    {
        return $entity instanceof ConstructionManager;
    }

    protected function getAllAttributes(): array
    {
        return [self::CONSTRUCTION_MANAGER_VIEW];
    }

    protected function getReadOnlyAttributes(): array
    {
        return [self::CONSTRUCTION_MANAGER_VIEW];
    }

    protected function getUnrelatedConstructionManagerAttributes(bool $isLimitedAccount): array
    {
        if ($isLimitedAccount) {
            return [];
        }

        return [self::CONSTRUCTION_MANAGER_VIEW];
    }
}
