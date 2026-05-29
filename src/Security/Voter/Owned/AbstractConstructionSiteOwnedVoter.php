<?php

namespace App\Security\Voter\Owned;

use App\Entity\ConstructionSite;
use App\Security\TokenTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template T
 */
abstract class AbstractConstructionSiteOwnedVoter extends Voter
{
    use TokenTrait;

    /**
     * @param T $subject
     */
    abstract protected function getConstructionSite(mixed $subject): ConstructionSite;

    /**
     * @param T $subject
     */
    abstract protected function isInstanceOf(mixed $subject): bool;

    abstract protected function getViewRole(): string;

    abstract protected function getModifyRole(): string;

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject !== null && $this->isInstanceOf($subject) && in_array($attribute, [$this->getViewRole(), $this->getModifyRole()]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $constructionSite = $this->getConstructionSite($subject);

        if (($constructionManager = $this->tryGetConstructionManager($token))) {
            if ($constructionManager->getCanAssociateSelf()) {
                return true;
            }

            return $constructionManager->getConstructionSites()->contains($constructionSite);
        } elseif (($craftsman = $this->tryGetCraftsman($token))) {
            return $craftsman->getConstructionSite() === $constructionSite && $attribute == $this->getViewRole();
        } elseif (($filter = $this->tryGetFilter($token))) {
            return $filter->getConstructionSite() === $constructionSite && $attribute == $this->getViewRole();
        } else {
            return false;
        }
    }
}
