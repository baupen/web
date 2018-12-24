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

use App\Api\Entity\Edit\UpdateMapFile;
use App\Api\Request\ConstructionSiteRequest;

class UpdateMapFileRequest extends ConstructionSiteRequest
{
    /**
     * @var UpdateMapFile
     */
    private $mapFile;

    /**
     * @return UpdateMapFile
     */
    public function getMapFile(): UpdateMapFile
    {
        return $this->mapFile;
    }

    /**
     * @param UpdateMapFile $mapFile
     */
    public function setMapFile(UpdateMapFile $mapFile): void
    {
        $this->mapFile = $mapFile;
    }
}
