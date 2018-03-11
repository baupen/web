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

class MarkerType extends BaseEnum
{
    const CREATED = 1;
    const RESOLVED = 2;
    const APPROVED = 3;
}
