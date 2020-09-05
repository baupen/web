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
use App\Entity\Craftsman;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CraftsmanVoter
{
    const CRAFTSMAN_VIEW = 'craftsman_view';
    const CRAFTSMAN_MODIFY = 'craftsman_modify';

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
        if (!in_array($attribute, [self::CRAFTSMAN_VIEW, self::CRAFTSMAN_MODIFY])) {
            return false;
        }

        return $subject instanceof Craftsman;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string    $attribute
     * @param Craftsman $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if ($user instanceof ConstructionManager) {
            switch ($attribute) {
                case self::CRAFTSMAN_VIEW:
                case self::CRAFTSMAN_MODIFY:
                    return $subject->getConstructionSite()->getConstructionManagers()->contains($user);
            }
        } elseif ($user instanceof Craftsman) {
            switch ($attribute) {
                case self::CRAFTSMAN_VIEW:
                    return $user === $subject;
                case self::CRAFTSMAN_MODIFY:
                    return false;
            }
        }

        throw new \LogicException('Attribute '.$attribute.' unknown!');
    }
}
