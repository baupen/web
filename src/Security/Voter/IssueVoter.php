<?php

namespace App\Security\Voter;

use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Security\TokenTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class IssueVoter extends Voter
{
    use TokenTrait;

    public const string ISSUE_VIEW = 'ISSUE_VIEW';
    public const string ISSUE_MODIFY = 'ISSUE_MODIFY';
    public const string ISSUE_RESPOND = 'ISSUE_RESPOND';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Issue && in_array($attribute, [self::ISSUE_VIEW, self::ISSUE_MODIFY, self::ISSUE_RESPOND]);
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param Issue  $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $constructionSite = $subject->getConstructionSite();
        if (($constructionManager = $this->tryGetConstructionManager($token))) {
            if ($constructionManager->getCanAssociateSelf()) {
                return true;
            }

            return $constructionManager->getConstructionSites()->contains($constructionSite);
        } elseif (($craftsman = $this->tryGetCraftsman($token))) {
            if ($subject->getCraftsman() !== $craftsman || $craftsman->getConstructionSite() !== $constructionSite) {
                return false;
            }

            if ($attribute == self::ISSUE_RESPOND) {
                return $craftsman->getCanEdit() && ($subject->getResolvedBy() === $craftsman || null === $subject->getResolvedBy());
            }

            return $attribute === self::ISSUE_VIEW;
        } elseif (($filter = $this->tryGetFilter($token))) {
            return $filter->getConstructionSite() === $constructionSite && $attribute == self::ISSUE_VIEW;
        } else {
            return false;
        }
    }
}
