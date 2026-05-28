<?php

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
