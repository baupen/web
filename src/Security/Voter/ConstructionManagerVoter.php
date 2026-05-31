<?php

namespace App\Security\Voter;

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Security\TokenTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ConstructionManagerVoter extends Voter
{
    use TokenTrait;

    public const string CONSTRUCTION_MANAGER_VIEW = 'CONSTRUCTION_MANAGER_VIEW';
    public const string CONSTRUCTION_MANAGER_MODIFY = 'CONSTRUCTION_MANAGER_MODIFY';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof ConstructionManager && in_array($attribute, [self::CONSTRUCTION_MANAGER_MODIFY, self::CONSTRUCTION_MANAGER_VIEW]);
    }

    /**
     * @param string $attribute
     * @param ConstructionManager $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (($constructionManager = $this->tryGetConstructionManager($token))) {
            if ($attribute == self::CONSTRUCTION_MANAGER_MODIFY) {
                return $constructionManager === $subject;
            }

            // this is not 100% precise; but its good enough
            // overall, leakage of construction managers not a problem, as need to guess GUID
            // note that there is the edge case of construction managers B leaving construction sites A, but B should remain accessible to all construction managers with access to A
            // also note that this voter may be called often, so cannot do expensive work in here
            return true;
        } else {
            return false;
        }
    }
}
