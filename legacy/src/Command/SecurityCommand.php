<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use SensioLabs\Security\Command\SecurityCheckerCommand;
use SensioLabs\Security\SecurityChecker;

class SecurityCommand extends SecurityCheckerCommand
{
    public function __construct()
    {
        parent::__construct(new SecurityChecker());
    }
}
