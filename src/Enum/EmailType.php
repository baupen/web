<?php

namespace App\Enum;

enum EmailType: int
{
    case REGISTER_CONFIRM = 1;
    case RECOVER_CONFIRM = 2;
    case APP_INVITATION = 3;
    case CRAFTSMAN_ISSUE_REMINDER = 4;
    case CONSTRUCTION_SITES_OVERVIEW = 5;
}
