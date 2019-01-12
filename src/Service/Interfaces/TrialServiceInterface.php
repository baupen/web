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

interface TrialServiceInterface
{
    /**
     * creates a trial account with prefilled data.
     *
     * @param string|null $proposedGivenName
     * @param string|null $proposedFamilyName
     *
     * @return ConstructionManager
     */
    public function createTrialAccount(?string $proposedGivenName = null, ?string $proposedFamilyName = null);
}
