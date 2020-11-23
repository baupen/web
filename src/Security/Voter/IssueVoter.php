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
use App\Entity\Issue;
use App\Security\TokenUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class IssueVoter extends Voter
{
    public const ISSUE_VIEW = 'ISSUE_VIEW';
    public const ISSUE_MODIFY = 'ISSUE_MODIFY';
    public const ISSUE_RESPOND = 'ISSUE_RESPOND';

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
        if (!in_array($attribute, [self::ISSUE_VIEW, self::ISSUE_MODIFY])) {
            return false;
        }

        return $subject instanceof Issue;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param Issue  $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if ($user instanceof ConstructionManager) {
            switch ($attribute) {
                case self::ISSUE_VIEW:
                case self::ISSUE_MODIFY:
                    return $subject->isConstructionSiteSet() && $subject->getConstructionSite()->getConstructionManagers()->contains($user);
            }
        } elseif ($user instanceof TokenUser) {
            if (null !== $user->getCraftsman()) {
                return in_array($attribute, [self::ISSUE_VIEW, self::ISSUE_RESPOND]) && $user->getCraftsman() === $user->getCraftsman();
            } else {
                return self::ISSUE_VIEW === $attribute && $user->getConstructionSite() === $subject->getConstructionSite();
            }
        }

        throw new \LogicException('Attribute '.$attribute.' unknown!');
    }
}
