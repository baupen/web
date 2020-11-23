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

use App\Entity\ConstructionSite;
use App\Security\Voter\Base\BaseVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ConstructionSiteVoter extends BaseVoter
{
    public const CONSTRUCTION_SITE_CREATE = 'CONSTRUCTION_SITE_CREATE';
    public const CONSTRUCTION_SITE_VIEW = 'CONSTRUCTION_SITE_VIEW';
    public const CONSTRUCTION_SITE_MODIFY = 'CONSTRUCTION_SITE_MODIFY';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string           $attribute An attribute
     * @param ConstructionSite $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::CONSTRUCTION_SITE_CREATE, self::CONSTRUCTION_SITE_VIEW, self::CONSTRUCTION_SITE_MODIFY])) {
            return false;
        }

        return $subject instanceof ConstructionSite;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string           $attribute
     * @param ConstructionSite $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $constructionManager = $this->tryGetConstructionManager($token);
        if (null !== $constructionManager) {
            if ($constructionManager->getIsTrialAccount() || $constructionManager->getIsExternalAccount()) {
                return in_array($attribute, [self::CONSTRUCTION_SITE_VIEW, self::CONSTRUCTION_SITE_MODIFY]) && $subject->getConstructionManagers()->contains($constructionManager);
            } else {
                if (self::CONSTRUCTION_SITE_MODIFY === $attribute) {
                    return $subject->getConstructionManagers()->contains($constructionManager);
                }

                return true;
            }
        }

        throw new \LogicException('Unknown user in token '.get_class($token));
    }
}
