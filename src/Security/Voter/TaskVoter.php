<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;

class TaskVoter extends ConstructionSiteOwnedEntityVoter
{
    public const TASK_VIEW = 'TASK_VIEW';
    public const TASK_MODIFY = 'TASK_MODIFY';

    protected function isInstanceOf($entity): bool
    {
        return $entity instanceof Task;
    }

    protected function getAllAttributes(): array
    {
        return [self::TASK_VIEW, self::TASK_MODIFY];
    }
}
