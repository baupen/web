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
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;

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
}
