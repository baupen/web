<?php

namespace App\Security\Voter\Internal;

use App\Entity\ConstructionSite;
use App\Security\TokenTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template T
 */
abstract class AbstractConstructionSiteInternalVoter extends Voter
{
    use TokenTrait;

    /**
     * @param T $subject
     */
    abstract protected function getConstructionSite(mixed $subject): ?ConstructionSite;

    /**
     * @param mixed $subject
     */
    abstract protected function isInstanceOf(mixed $subject): bool;

    abstract protected function getRoles(): array;

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject !== null && $this->isInstanceOf($subject) && in_array($attribute, $this->getRoles());
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $constructionSite = $this->getConstructionSite($subject);

        if (($constructionManager = $this->tryGetConstructionManager($token))) {
            if ($constructionManager->getCanAssociateSelf()) {
                return true;
            }

            return $constructionManager->getConstructionSites()->contains($constructionSite);
        } else {
            return false;
        }
    }
}
