<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Base;

class MapFile extends BaseEntity
{
    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string|null
     */
    private $mapId;

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

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
