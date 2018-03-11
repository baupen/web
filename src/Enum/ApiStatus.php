<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Enum;

use App\Enum\Base\BaseEnum;

class ApiStatus extends BaseEnum
{
    const SUCCESSFUL = 0;
    const EMPTY_REQUEST = 1;
    const UNKNOWN_IDENTIFIER = 2;
    const WRONG_PASSWORD = 3;
    const INVALID_AUTHENTICATION_TOKEN = 4;
    const EXECUTION_FAILED = 5;
}
