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

use App\Entity\ConstructionSite;

interface SyncServiceInterface
{
    /**
     * syncs the filesystem with the database, creating/updating construction sites as needed.
     */
    public function sync();

    /**
     * syncs single construction site with the filesystem.
     *
     * @param ConstructionSite $constructionSite
     * @param bool $skipCacheWarmup
     */
    public function syncConstructionSite(ConstructionSite $constructionSite, bool $skipCacheWarmup = false);
}
