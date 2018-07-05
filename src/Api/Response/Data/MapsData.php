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

use App\Api\Entity\Base\Map;

class MapsData
{
    /**
     * @var Map[]
     */
    private $maps;

    /**
     * @return Map[]
     */
    public function getMaps(): array
    {
        return $this->maps;
    }

    /**
     * @param Map[] $maps
     */
    public function setMaps(array $maps): void
    {
        $this->maps = $maps;
    }
}
