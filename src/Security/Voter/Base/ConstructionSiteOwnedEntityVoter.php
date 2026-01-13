<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter\Base;

use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;

abstract class ConstructionSiteOwnedEntityVoter extends ConstructionSiteRelatedEntityVoter
{
    /**
     * @param ConstructionSiteOwnedEntityInterface $subject
     */
    protected function isConstructionManagerRelated(ConstructionManager $constructionManager, $subject): bool
    {
        return $subject->isConstructionSiteSet() && $constructionManager->getConstructionSites()->contains($subject->getConstructionSite());
    }

    /**
     * @param ConstructionSiteOwnedEntityInterface $subject
     */
    protected function isCraftsmanRelated(Craftsman $craftsman, $subject): bool
    {
        return $craftsman->isConstructionSiteSet() && $subject->isConstructionSiteSet() && $craftsman->getConstructionSite() === $subject->getConstructionSite();
    }

    /**
     * @param ConstructionSiteOwnedEntityInterface $subject
     */
    protected function isFilterRelated(Filter $filter, $subject): bool
    {
        return $filter->isConstructionSiteSet() && $subject->isConstructionSiteSet() && $filter->getConstructionSite() === $subject->getConstructionSite();
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string                               $attribute An attribute
     * @param ConstructionSiteOwnedEntityInterface $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject): bool
    {
        $support = parent::supports($attribute, $subject);

        if ($support && !$subject instanceof ConstructionSiteOwnedEntityInterface) {
            throw new \Exception('Must implement ConstructionSiteOwnedEntityInterface to use this voter.');
        }

        return $support;
    }
}
