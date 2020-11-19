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

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;

interface TrialServiceInterface
{
    /**
     * creates a trial construction site.
     */
    public function createTrialConstructionSite(ConstructionManager $constructionManager): ConstructionSite;
}
