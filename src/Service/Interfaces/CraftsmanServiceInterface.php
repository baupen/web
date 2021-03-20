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

use App\Entity\Craftsman;
use App\Service\Craftsman\Statistics;

interface CraftsmanServiceInterface
{
    /**
     * @param Craftsman[] $craftsmen
     *
     * @return Statistics[]
     */
    public function createStatisticLookup(array $craftsmen): array;

    public function getCurrentAndPastSummaryLookup(array $craftsmen, \DateTime $pastDate): array;
}
