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

class UploadFileCheckStatus extends BaseEnum
{
    const OK = 1;
    const FILE_ALREADY_EXISTS = 2;
    const HASH_CONFLICTS_FOUND = 3;
}
