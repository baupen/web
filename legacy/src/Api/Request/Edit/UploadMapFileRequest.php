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

use App\Api\Entity\Edit\UploadMapFile;
use App\Api\Request\ConstructionSiteRequest;

class UploadMapFileRequest extends ConstructionSiteRequest
{
    /**
     * @var UploadMapFile
     */
    private $mapFile;

    public function getMapFile(): UploadMapFile
    {
        return $this->mapFile;
    }

    public function setMapFile(UploadMapFile $mapFile): void
    {
        $this->mapFile = $mapFile;
    }
}
