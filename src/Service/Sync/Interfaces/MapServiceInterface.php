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

interface MapServiceInterface
{
    public function syncConstructionSiteMaps(SyncTransaction $syncTransaction, ConstructionSite $constructionSite);
}
