<?php

namespace App\Enum;

enum EmailTemplatePurpose: int
{
    case OPEN_ISSUES = 1;
    case UNREAD_ISSUES = 2;
    case OVERDUE_ISSUES = 3;
}
