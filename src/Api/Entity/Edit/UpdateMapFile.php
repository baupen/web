<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Edit;

class UpdateMapFile
{
    /**
     * @var string|null
     */
    private $mapId;

    /**
     * @return string|null
     */
    public function getMapId(): ?string
    {
        return $this->mapId;
    }

    /**
     * @param string|null $mapId
     */
    public function setMapId(?string $mapId): void
    {
        $this->mapId = $mapId;
    }
}
