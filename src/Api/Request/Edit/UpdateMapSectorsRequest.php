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

use App\Api\Entity\Edit\UpdateMapSector;
use App\Api\Request\ConstructionSiteRequest;

class UpdateMapSectorsRequest extends ConstructionSiteRequest
{
    /**
     * @var UpdateMapSector[]|array
     */
    private $updateMapSectors;

    /**
     * @return UpdateMapSector[]|array
     */
    public function getUpdateMapSectors()
    {
        return $this->updateMapSectors;
    }

    /**
     * @param UpdateMapSector[]|array $updateMapSectors
     */
    public function setUpdateMapSectors($updateMapSectors): void
    {
        $this->updateMapSectors = $updateMapSectors;
    }
}
