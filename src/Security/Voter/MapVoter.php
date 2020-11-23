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
use App\Entity\Map;
use App\Security\Voter\Base\BaseVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MapVoter extends BaseVoter
{
    public const MAP_VIEW = 'MAP_VIEW';
    public const MAP_MODIFY = 'MAP_MODIFY';

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
        if (!in_array($attribute, [self::MAP_VIEW, self::MAP_MODIFY])) {
            return false;
        }

        return $subject instanceof Map;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param Map    $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $constructionManager = $this->tryGetConstructionManager($token);
        if (null !== $constructionManager) {
            return in_array($attribute, [self::MAP_VIEW, self::MAP_MODIFY]) &&
                $subject->isConstructionSiteSet() &&
                $subject->getConstructionSite()->getConstructionManagers()->contains($constructionManager);
        }

        $craftsman = $this->tryGetCraftsman($token);
        if (null !== $craftsman) {
            return self::MAP_VIEW === $attribute &&
                $craftsman->getConstructionSite() === $subject->getConstructionSite();
        }

        $filter = $this->tryGetFilter($token);
        if (null !== $filter) {
            return self::MAP_VIEW === $attribute &&
                $filter->getConstructionSite() === $subject->getConstructionSite() &&
                (null === $filter->getMapIds() || in_array($subject->getId(), $filter->getMapIds()));
        }

        throw new \LogicException('Unknown user in token '.get_class($token));
    }
}
