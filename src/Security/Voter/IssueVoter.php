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

    protected function getRelatedCraftsmanAccessibleAttributes(): array
    {
        return array_merge($this->getReadOnlyAttributes(), [self::ISSUE_RESPOND]);
    }

    /**
     * @param Issue $subject
     */
    protected function isIncludedInFilter(Filter $filter, $attribute, $subject): bool
    {
        $listsValid = (null === $filter->getCraftsmanIds() || in_array($subject->getCraftsman()->getId(), $filter->getCraftsmanIds())) &&
            (null === $filter->getMapIds() || in_array($subject->getMap()->getId(), $filter->getMapIds())) &&
            (null === $filter->getNumbers() || in_array($subject->getNumber(), $filter->getNumbers()));

        if (!$listsValid) {
            return false;
        }

        $booleansValid = (null === $filter->getIsMarked() || $subject->getIsMarked() === $filter->getIsMarked()) &&
            (null === $filter->getWasAddedWithClient() || $subject->getWasAddedWithClient() === $filter->getWasAddedWithClient()) &&
            (null === $filter->getIsDeleted() || $subject->getIsDeleted() === $filter->getIsDeleted());

        if (!$booleansValid) {
            return false;
        }

        if (null !== $filter->getDescription() && false === strpos($subject->getDescription(), $filter->getDescription())) {
            return false;
        }

        $dateTimeMethods = ['Deadline', 'CreatedAt', 'RegisteredAt', 'ResolvedAt', 'ClosedAt'];
        foreach ($dateTimeMethods as $dateTimeMethod) {
            $getter = 'get'.$dateTimeMethod;
            $realValue = $subject->$getter();

            $beforeGetter = $getter.'Before';
            $beforeValue = $filter->$beforeGetter();
            // value must be null or before
            if (null !== $beforeValue && null !== $realValue && $beforeValue < $realValue) {
                return false;
            }

            $afterGetter = $getter.'After';
            $afterValue = $filter->$afterGetter();
            // value must not be null and after
            if (null !== $afterValue && (null === $realValue || $realValue < $afterValue)) {
                return false;
            }
        }

        return true;
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
