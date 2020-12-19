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
use App\Security\TokenTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class ConstructionSiteRelatedEntityVoter extends Voter
{
    use TokenTrait;

    abstract protected function isConstructionManagerRelated(ConstructionManager $constructionManager, $subject);

    abstract protected function isCraftsmanRelated(Craftsman $craftsman, $subject);

    abstract protected function isFilterRelated(Filter $filter, $subject);

    abstract protected function isInstanceOf($entity): bool;

    abstract protected function getAllAttributes(): array;

    protected function getReadOnlyAttributes(): array
    {
        return [];
    }

    protected function getRelatedConstructionManagerAttributes(bool $isLimitedAccount): array
    {
        return $this->getAllAttributes();
    }

    protected function getUnrelatedConstructionManagerAttributes(bool $isLimitedAccount): array
    {
        return [];
    }

    protected function getRelatedCraftsmanAccessibleAttributes(): array
    {
        return $this->getReadOnlyAttributes();
    }

    protected function getRelatedFilterAccessibleAttributes(): array
    {
        return $this->getReadOnlyAttributes();
    }

    protected function isIncludedInFilter(Filter $filter, $attribute, $subject): bool
    {
        return true;
    }

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
        if (!in_array($attribute, $this->getAllAttributes())) {
            return false;
        }

        return $this->isInstanceOf($subject);
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $constructionManager = $this->tryGetConstructionManager($token);
        if (null !== $constructionManager) {
            $isConstructionManagerRelatedWithSubject = $this->isConstructionManagerRelated($constructionManager, $subject);
            $isLimitedAccount = $constructionManager->getIsExternalAccount() || $constructionManager->getIsTrialAccount();
            if ($isConstructionManagerRelatedWithSubject && in_array($attribute, $this->getRelatedConstructionManagerAttributes($isLimitedAccount))) {
                return true;
            }

            return in_array($attribute, $this->getUnrelatedConstructionManagerAttributes($isLimitedAccount));
        }

        $craftsman = $this->tryGetCraftsman($token);
        if (null !== $craftsman) {
            return in_array($attribute, $this->getRelatedCraftsmanAccessibleAttributes()) &&
                $this->isCraftsmanRelated($craftsman, $subject);
        }

        $filter = $this->tryGetFilter($token);
        if (null !== $filter) {
            return in_array($attribute, $this->getRelatedFilterAccessibleAttributes()) &&
                $this->isFilterRelated($filter, $subject) &&
                $this->isIncludedInFilter($filter, $attribute, $subject);
        }

        throw new \LogicException('Unknown user in token '.get_class($token));
    }
}
