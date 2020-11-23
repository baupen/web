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

use App\Entity\AuthenticationToken;
use App\Entity\ConstructionSite;
use App\Security\Voter\Base\BaseVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AuthenticationTokenVoter extends BaseVoter
{
    public const AUTHENTICATION_TOKEN_CREATE = 'AUTHENTICATION_TOKEN_CREATE';
    public const AUTHENTICATION_TOKEN_VIEW = 'AUTHENTICATION_TOKEN_VIEW';

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
        if (!in_array($attribute, [self::AUTHENTICATION_TOKEN_CREATE, self::AUTHENTICATION_TOKEN_VIEW])) {
            return false;
        }

        return $subject instanceof AuthenticationToken;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string              $attribute
     * @param AuthenticationToken $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $constructionManager = $this->tryGetConstructionManager($token);
        if (null === $constructionManager) {
            return false;
        }

        if ($subject->getConstructionManager()) {
            return $constructionManager === $subject->getConstructionManager();
        }

        $constructionSites = $constructionManager->getConstructionSites();
        if ($subject->getCraftsman()) {
            return $constructionSites->contains($subject->getCraftsman()->getConstructionSite());
        }

        if ($subject->getFilter()) {
            return $constructionSites->contains($subject->getFilter()->getConstructionSite());
        }

        return false;
    }
}
