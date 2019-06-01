<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Response\Data\Edit;

use App\Api\Entity\Edit\MapSector;

class MapSectorsData
{
    /**
     * @var MapSector[]
     */
    private $mapSectors;

    /**
     * @return MapSector[]
     */
    public function getMapSectors(): array
    {
        return $this->mapSectors;
    }

    /**
     * @param MapSector[] $mapSectors
     */
    public function setMapSectors(array $mapSectors): void
    {
        $this->mapSectors = $mapSectors;
    }
}
