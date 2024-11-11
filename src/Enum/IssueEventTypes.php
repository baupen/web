<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Enum;

enum IssueEventTypes: string
{
    case Text = 'TEXT';
    case StatusSet = 'STATUS_SET';
    case StatusUnset = 'STATUS_UNSET';
    case Email = 'EMAIL';
    case Image = 'IMAGE';
    case File = 'FILE';

    public static function isManualEvent(IssueEventTypes $candidate): bool
    {
        return in_array($candidate, [self::Text, self::Image, self::File], true);
    }
}
