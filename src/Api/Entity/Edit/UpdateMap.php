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

class UpdateMap
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
    private $isAutomaticEditEnabled;

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
    public function getIsAutomaticEditEnabled(): bool
    {
        return $this->isAutomaticEditEnabled;
    }

    /**
     * @param bool $isAutomaticEditEnabled
     */
    public function setIsAutomaticEditEnabled(bool $isAutomaticEditEnabled): void
    {
        $this->isAutomaticEditEnabled = $isAutomaticEditEnabled;
    }
}
