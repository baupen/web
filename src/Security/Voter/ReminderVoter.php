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

use App\Entity\Reminder;
use App\Security\Voter\Base\ConstructionSiteOwnedEntityVoter;

class ReminderVoter extends ConstructionSiteOwnedEntityVoter
{
    public const REMINDER_VIEW = 'REMINDER_VIEW';
    public const REMINDER_MODIFY = 'REMINDER_MODIFY';

    protected function isInstanceOf($entity): bool
    {
        return $entity instanceof Reminder;
    }

    protected function getAllAttributes(): array
    {
        return [self::REMINDER_VIEW, self::REMINDER_MODIFY];
    }
}
