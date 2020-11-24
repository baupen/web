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

use App\Entity\Filter;
use App\Entity\Issue;
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class IssueVoter extends ConstructionSiteOwnedEntityVoter
{
    public const ISSUE_VIEW = 'ISSUE_VIEW';
    public const ISSUE_MODIFY = 'ISSUE_MODIFY';
    public const ISSUE_RESPOND = 'ISSUE_RESPOND';

    protected function isInstanceOf($entity): bool
    {
        return $entity instanceof Issue;
    }

    protected function getAllAttributes(): array
    {
        return [self::ISSUE_VIEW, self::ISSUE_MODIFY, self::ISSUE_RESPOND];
    }

    protected function getReadOnlyAttributes(): array
    {
        return [self::ISSUE_VIEW];
    }

    protected function getCraftsmanAccessibleAttributes(): array
    {
        return array_merge($this->getReadOnlyAttributes(), [self::ISSUE_RESPOND]);
    }

    /**
     * @param Issue $subject
     */
    protected function isIncludedInFilter(Filter $filter, $attribute, $subject): bool
    {
        return (null === $filter->getCraftsmanIds() || in_array($subject->getCraftsman()->getId(), $filter->getCraftsmanIds())) &&
            (null === $filter->getCraftsmanTrades() || in_array($subject->getCraftsman()->getTrade(), $filter->getCraftsmanTrades())) &&
            (null === $filter->getMapIds() || in_array($subject->getMap()->getId(), $filter->getMapIds()));

        // TODO: Fully implement filter properties #350
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
        $craftsman = $this->tryGetCraftsman($token);
        if (null !== $craftsman && self::ISSUE_RESPOND === $attribute) {
            return $subject->getCraftsman() === $craftsman && ($subject->getResolvedBy() === $craftsman || null === $subject->getResolvedBy());
        }

        return parent::voteOnAttribute($attribute, $subject, $token);
    }
}
