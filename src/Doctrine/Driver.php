<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Doctrine;

use Doctrine\DBAL\Driver\PDOMySql;

class Driver extends PDOMySql\Driver
{
    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return new Platform();
    }
}
