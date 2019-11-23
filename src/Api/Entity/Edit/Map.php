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

use DateTime;

class Map extends \App\Api\Entity\Base\Map
{
    /**
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $parentId;

    /**
     * @var string|null
     */
    private $fileId;

    /**
     * @var int
     */
    private $issueCount;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getFileId(): ?string
    {
        return $this->fileId;
    }

    public function setFileId(?string $fileId): void
    {
        $this->fileId = $fileId;
    }

    public function getIssueCount(): int
    {
        return $this->issueCount;
    }

    public function setIssueCount(int $issueCount): void
    {
        $this->issueCount = $issueCount;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
