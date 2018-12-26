<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Sync\Interfaces;

use App\Entity\ConstructionSite;
use App\Model\SyncTransaction;

interface ConstructionSiteServiceInterface
{
    /**
     * @param SyncTransaction $syncTransaction
     * @param string $directory
     */
    public function addConstructionSite(SyncTransaction $syncTransaction, string $directory);

    /**
     * @param SyncTransaction $syncTransaction
     * @param ConstructionSite $constructionSite
     */
    public function syncConstructionSite(SyncTransaction $syncTransaction, ConstructionSite $constructionSite);
}
