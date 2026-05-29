<?php

namespace App\Security\Voter;

use App\Entity\ConstructionSite;
use App\Security\TokenTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ConstructionSiteVoter extends Voter
{
    use TokenTrait;

    public const string CONSTRUCTION_SITE_CREATE = 'CONSTRUCTION_SITE_CREATE';
    public const string CONSTRUCTION_SITE_VIEW = 'CONSTRUCTION_SITE_VIEW';
    public const string CONSTRUCTION_SITE_MODIFY = 'CONSTRUCTION_SITE_MODIFY';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof ConstructionSite && in_array($attribute, [self::CONSTRUCTION_SITE_CREATE, self::CONSTRUCTION_SITE_MODIFY, self::CONSTRUCTION_SITE_VIEW]);
    }

    /**
     * @param string $attribute
     * @param ConstructionSite $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (($constructionManager = $this->tryGetConstructionManager($token))) {
            if ($constructionManager->getCanAssociateSelf()) {
                return true;
            }

            if ($attribute == self::CONSTRUCTION_SITE_CREATE) {
                return false;
            }

            return $constructionManager->getConstructionSites()->contains($subject);
        } else {
            return false;
        }
    }
}
