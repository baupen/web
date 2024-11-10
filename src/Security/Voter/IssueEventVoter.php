<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter;

use App\Entity\IssueEvent;
use App\Enum\IssueEventTypes;
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class IssueEventVoter extends ConstructionSiteOwnedEntityVoter
{
    public const ISSUE_EVENT_VIEW = 'ISSUE_EVENT_VIEW';
    public const ISSUE_EVENT_MODIFY = 'ISSUE_EVENT_MODIFY';

    protected function isInstanceOf($entity): bool
    {
        return $entity instanceof IssueEvent;
    }

    protected function getAllAttributes(): array
    {
        return [self::ISSUE_EVENT_VIEW, self::ISSUE_EVENT_MODIFY];
    }

    protected function getReadOnlyAttributes(): array
    {
        return [self::ISSUE_EVENT_VIEW];
    }

    /**
     * @param IssueEvent $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        // disable editing of read-only events
        if (self::ISSUE_EVENT_VIEW !== $attribute && !in_array($subject->getType(), [IssueEventTypes::Text, IssueEventTypes::Image, IssueEventTypes::File], true)) {
            return false;
        }

        return parent::voteOnAttribute($attribute, $subject, $token);
    }
}
