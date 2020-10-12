<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Enum;

use App\Enum\Base\BaseEnum;

class EmailType extends BaseEnum
{
    const REGISTER_CONFIRM = 1;
    const RECOVER_CONFIRM = 2;
    const APP_INVITATION = 3;
}
