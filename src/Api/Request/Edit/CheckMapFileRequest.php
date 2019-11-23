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

use App\Api\Entity\Edit\CheckMapFile;
use App\Api\Request\ConstructionSiteRequest;

class CheckMapFileRequest extends ConstructionSiteRequest
{
    /**
     * @var CheckMapFile
     */
    private $mapFile;

    public function getMapFile(): CheckMapFile
    {
        return $this->mapFile;
    }

    public function setMapFile(CheckMapFile $mapFile): void
    {
        $this->mapFile = $mapFile;
    }
}
