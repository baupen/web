<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Entity;

use App\Api\External\Entity\Base\BaseEntity;

class Map extends BaseEntity
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $children;

    /**
     * @var string[]
     */
    private $issues;

    /**
     * @var MapSector[]|null
     */
    private $sectors;

    /**
     * @var File|null
     */
    private $file;

    /**
     * @var Frame|null
     */
    private $sectorFrame;

    /**
     * @var string
     */
    private $constructionSiteID;

    /**
     * @var string|null
     */
    private $parentID;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param string[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * @return string[]
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    /**
     * @param string[] $issues
     */
    public function setIssues(array $issues): void
    {
        $this->issues = $issues;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): void
    {
        $this->file = $file;
    }

    /**
     * @return MapSector[]
     */
    public function getSectors(): ?array
    {
        return $this->sectors;
    }

    /**
     * @param MapSector[] $sectors
     */
    public function setSectors(?array $sectors): void
    {
        $this->sectors = $sectors;
    }

    public function getSectorFrame(): ?Frame
    {
        return $this->sectorFrame;
    }

    public function setSectorFrame(?Frame $sectorFrame): void
    {
        $this->sectorFrame = $sectorFrame;
    }

    public function getConstructionSiteID(): string
    {
        return $this->constructionSiteID;
    }

    public function setConstructionSiteID(string $constructionSiteID): void
    {
        $this->constructionSiteID = $constructionSiteID;
    }

    public function getParentID(): ?string
    {
        return $this->parentID;
    }

    public function setParentID(?string $parentID): void
    {
        $this->parentID = $parentID;
    }
}
