<?php

/*
 * This file is part of the mangel.io project.
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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class ConstructionSiteOwnedEntityVoter extends BaseVoter
{
    abstract protected function isExpectedConstructionSiteOwnedEntityInstance(ConstructionSiteOwnedEntityInterface $constructionSiteOwnedEntity): bool;

    abstract protected function getAttributes(): array;

    abstract protected function getConstructionManagerAccessibleAttributes(ConstructionManager $manager): array;

    abstract protected function getCraftsmanAccessibleAttributes(Craftsman $craftsman): array;

    abstract protected function getFilterAccessibleAttributes(Filter $filter): array;

    abstract protected function isIncludedInFilter(Filter $filter, $attribute, $subject): bool;

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string                               $attribute An attribute
     * @param ConstructionSiteOwnedEntityInterface $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, $this->getAttributes())) {
            return false;
        }

        return $subject instanceof ConstructionSiteOwnedEntityInterface && $this->isExpectedConstructionSiteOwnedEntityInstance($subject);
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
        $constructionManager = $this->tryGetConstructionManager($token);
        if (null !== $constructionManager) {
            return in_array($attribute, $this->getConstructionManagerAccessibleAttributes($constructionManager)) &&
                $subject->isConstructionSiteSet() &&
                $subject->getConstructionSite()->getConstructionManagers()->contains($constructionManager);
        }

        $craftsman = $this->tryGetCraftsman($token);
        if (null !== $craftsman) {
            return in_array($attribute, $this->getCraftsmanAccessibleAttributes($craftsman)) &&
                $craftsman->getConstructionSite() === $subject->getConstructionSite();
        }

        $filter = $this->tryGetFilter($token);
        if (null !== $filter) {
            return in_array($attribute, $this->getFilterAccessibleAttributes($filter)) &&
                $filter->getConstructionSite() === $subject->getConstructionSite() &&
                $this->isIncludedInFilter($filter, $attribute, $subject);
        }

        throw new \LogicException('Unknown user in token '.get_class($token));
    }
}
