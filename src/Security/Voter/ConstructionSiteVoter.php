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
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ConstructionSiteVoter extends ConstructionSiteOwnedEntityVoter
{
    public const CONSTRUCTION_SITE_CREATE = 'CONSTRUCTION_SITE_CREATE';
    public const CONSTRUCTION_SITE_VIEW = 'CONSTRUCTION_SITE_VIEW';
    public const CONSTRUCTION_SITE_MODIFY = 'CONSTRUCTION_SITE_MODIFY';

    protected function isExpectedConstructionSiteOwnedEntityInstance(ConstructionSiteOwnedEntityInterface $constructionSiteOwnedEntity): bool
    {
        return $constructionSiteOwnedEntity instanceof ConstructionSite;
    }

    protected function getAttributes(): array
    {
        return [self::CONSTRUCTION_SITE_CREATE, self::CONSTRUCTION_SITE_VIEW, self::CONSTRUCTION_SITE_MODIFY];
    }

    protected function getConstructionManagerAccessibleAttributes(ConstructionManager $manager): array
    {
        return [self::CONSTRUCTION_SITE_VIEW, self::CONSTRUCTION_SITE_MODIFY];
    }

    protected function getCraftsmanAccessibleAttributes(Craftsman $craftsman): array
    {
        return [self::CONSTRUCTION_SITE_VIEW];
    }

    protected function getFilterAccessibleAttributes(Filter $filter): array
    {
        return [self::CONSTRUCTION_SITE_VIEW];
    }

    protected function isIncludedInFilter(Filter $filter, $attribute, $subject): bool
    {
        return $filter->getConstructionSite() === $subject;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string                               $attribute
     * @param ConstructionSiteOwnedEntityInterface $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        // can only create if full user (not trial, not external)
        if (self::CONSTRUCTION_SITE_CREATE === $attribute) {
            $constructionManager = $this->tryGetConstructionManager($token);

            return null !== $constructionManager && !$constructionManager->getIsExternalAccount() && !$constructionManager->getIsTrialAccount();
        }

        return parent::voteOnAttribute($attribute, $subject, $token);
    }
}
