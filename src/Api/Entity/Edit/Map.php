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

use App\Api\Entity\Base\BaseEntity;

class Map extends BaseEntity
{
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
     * @var bool
     */
    private $preventAutomaticEdit;

    /**
     * @var int
     */
    private $issueCount;

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
     * @return string|null
     */
    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    /**
     * @param string|null $parentId
     */
    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    /**
     * @return string|null
     */
    public function getFileId(): ?string
    {
        return $this->fileId;
    }

    /**
     * @param string|null $fileId
     */
    public function setFileId(?string $fileId): void
    {
        $this->fileId = $fileId;
    }

    /**
     * @return bool
     */
    public function isPreventAutomaticEdit(): bool
    {
        return $this->preventAutomaticEdit;
    }

    /**
     * @param bool $preventAutomaticEdit
     */
    public function setPreventAutomaticEdit(bool $preventAutomaticEdit): void
    {
        $this->preventAutomaticEdit = $preventAutomaticEdit;
    }

    /**
     * @return int
     */
    public function getIssueCount(): int
    {
        return $this->issueCount;
    }

    /**
     * @param int $issueCount
     */
    public function setIssueCount(int $issueCount): void
    {
        $this->issueCount = $issueCount;
    }
}
