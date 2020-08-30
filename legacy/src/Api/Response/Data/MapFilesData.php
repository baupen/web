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

class MapFilesData
{
    /**
     * @var MapFile[]
     */
    private $mapFiles;

    /**
     * @return MapFile[]
     */
    public function getMapFiles(): array
    {
        return $this->mapFiles;
    }

    /**
     * @param MapFile[] $mapFiles
     */
    public function setMapFiles(array $mapFiles): void
    {
        $this->mapFiles = $mapFiles;
    }
}
