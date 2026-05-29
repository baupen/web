<?php

namespace App\Security\Voter\Internal;

use App\Entity\ConstructionSite;
use App\Entity\Task;

/**
 * @implements AbstractConstructionSiteInternalVoter<Task>
 */
class TaskVoter extends AbstractConstructionSiteInternalVoter
{
    public const string TASK_VIEW = 'TASK_VIEW';
    public const string TASK_MODIFY = 'TASK_MODIFY';

    protected function isInstanceOf(mixed $subject): bool
    {
        return $subject instanceof Task;
    }

    protected function getConstructionSite(mixed $subject): ConstructionSite
    {
        return $subject->getConstructionSite();
    }

    protected function getRoles(): array
    {
        return [self::TASK_VIEW, self::TASK_MODIFY];
    }
}
