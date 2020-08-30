<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Request\Edit;

use App\Api\Entity\Edit\UpdateExternalConstructionManager;
use App\Api\Request\ConstructionSiteRequest;

class UpdateExternalConstructionManagerRequest extends ConstructionSiteRequest
{
    /**
     * @var UpdateExternalConstructionManager
     */
    private $externalConstructionManager;

    public function getExternalConstructionManager(): UpdateExternalConstructionManager
    {
        return $this->externalConstructionManager;
    }

    public function setExternalConstructionManager(UpdateExternalConstructionManager $externalConstructionManager): void
    {
        $this->externalConstructionManager = $externalConstructionManager;
    }
}
