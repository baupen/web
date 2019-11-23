<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data;

use App\Api\Entity\Base\MapFile;

class MapFileData
{
    /**
     * @var MapFile
     */
    private $mapFile;

    public function getMapFile(): MapFile
    {
        return $this->mapFile;
    }

    public function setMapFile(MapFile $mapFile): void
    {
        $this->mapFile = $mapFile;
    }
}
