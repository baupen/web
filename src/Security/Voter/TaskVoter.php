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
