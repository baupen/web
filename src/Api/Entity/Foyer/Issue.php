<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Foyer;

class Issue extends \App\Api\Entity\Base\Issue
{
    /**
     * @var bool
     */
    private $isMarked;

    /**
     * @var bool
     */
    private $wasAddedWithClient;

    /**
     * @var string|null
     */
    private $craftsmanId;

    /**
     * @var string
     */
    private $map;

    /**
     * @var \DateTime|null
     */
    private $uploadedAt;

    /**
     * @var string|null
     */
    private $uploadByName;

    /**
     * @var string|null
     */
    private $imageThumbnail;

    /**
     * @var string|null
     */
    private $imageFull;

    /**
     * @return bool
     */
    public function getIsMarked(): bool
    {
        return $this->isMarked;
    }

    /**
     * @param bool $isMarked
     */
    public function setIsMarked(bool $isMarked): void
    {
        $this->isMarked = $isMarked;
    }

    /**
     * @return bool
     */
    public function getWasAddedWithClient(): bool
    {
        return $this->wasAddedWithClient;
    }

    /**
     * @param bool $wasAddedWithClient
     */
    public function setWasAddedWithClient(bool $wasAddedWithClient): void
    {
        $this->wasAddedWithClient = $wasAddedWithClient;
    }

    /**
     * @return string|null
     */
    public function getCraftsmanId(): ?string
    {
        return $this->craftsmanId;
    }

    /**
     * @param string|null $craftsmanId
     */
    public function setCraftsmanId(?string $craftsmanId): void
    {
        $this->craftsmanId = $craftsmanId;
    }

    /**
     * @return string|null
     */
    public function getMap(): ?string
    {
        return $this->map;
    }

    /**
     * @param string|null $map
     */
    public function setMap(?string $map): void
    {
        $this->map = $map;
    }

    /**
     * @return \DateTime|null
     */
    public function getUploadedAt(): ?\DateTime
    {
        return $this->uploadedAt;
    }

    /**
     * @param \DateTime|null $uploadedAt
     */
    public function setUploadedAt(?\DateTime $uploadedAt): void
    {
        $this->uploadedAt = $uploadedAt;
    }

    /**
     * @return null|string
     */
    public function getUploadByName(): ?string
    {
        return $this->uploadByName;
    }

    /**
     * @param null|string $uploadByName
     */
    public function setUploadByName(?string $uploadByName): void
    {
        $this->uploadByName = $uploadByName;
    }

    /**
     * @return null|string
     */
    public function getImageThumbnail(): ?string
    {
        return $this->imageThumbnail;
    }

    /**
     * @param null|string $imageThumbnail
     */
    public function setImageThumbnail(?string $imageThumbnail): void
    {
        $this->imageThumbnail = $imageThumbnail;
    }

    /**
     * @return null|string
     */
    public function getImageFull(): ?string
    {
        return $this->imageFull;
    }

    /**
     * @param null|string $imageFull
     */
    public function setImageFull(?string $imageFull): void
    {
        $this->imageFull = $imageFull;
    }
}
