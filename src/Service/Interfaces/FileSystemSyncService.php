<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

interface FileSystemSyncService
{
    /**
     * syncs the filesystem with the database, creating/updating construction sites as needed.
     */
    public function sync();
}
