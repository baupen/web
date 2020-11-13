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
use App\Entity\Filter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FilterVoter extends Voter
{
    public const FILTER_CREATE = 'FILTER_CREATE';
    public const FILTER_VIEW = 'FILTER_VIEW';

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
        if (!in_array($attribute, [self::FILTER_CREATE, self::FILTER_VIEW])) {
            return false;
        }

        return $subject instanceof Filter;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param Filter $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if ($user instanceof ConstructionManager) {
            switch ($attribute) {
                case self::FILTER_VIEW:
                case self::FILTER_CREATE:
                    return $subject->isConstructionSiteSet() && $subject->getConstructionSite()->getConstructionManagers()->contains($user);
            }
        }

        if (self::FILTER_VIEW === $attribute) {
            return null === $subject->getAccessAllowedUntil() || $subject->getAccessAllowedUntil() > new \DateTime();
        }

        return false;
    }
}
