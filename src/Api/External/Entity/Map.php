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
     * @var string
     */
    private $buildingID;

    /**
     * @var File|null
     */
    private $file;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
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

    /**
     * @return string
     */
    public function getBuildingID(): string
    {
        return $this->buildingID;
    }

    /**
     * @param string $buildingID
     */
    public function setBuildingID(string $buildingID): void
    {
        $this->buildingID = $buildingID;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File|null $file
     */
    public function setFile(?File $file): void
    {
        $this->file = $file;
    }
}
