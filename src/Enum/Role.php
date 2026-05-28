<?php

namespace App\Enum;

enum Role: string
{
    // can use any features & impersonate users
    case ADMIN = 'ROLE_ADMIN';

    // can use any features
    case CONSTRUCTION_MANAGER = 'ROLE_CONSTRUCTION_MANAGER';

    // can not see data related to other construction sites (including the other construction sites itself)
    case ASSOCIATED_CONSTRUCTION_MANAGER = 'ROLE_ASSOCIATED_CONSTRUCTION_MANAGER';
}
