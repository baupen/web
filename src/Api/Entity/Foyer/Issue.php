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

use DateTime;

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
     * @var DateTime|null
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
     * @var DateTime|null
     */
    private $responseLimit;

    public function getIsMarked(): bool
    {
        return $this->isMarked;
    }

    public function setIsMarked(bool $isMarked): void
    {
        $this->isMarked = $isMarked;
    }

    public function getWasAddedWithClient(): bool
    {
        return $this->wasAddedWithClient;
    }

    public function setWasAddedWithClient(bool $wasAddedWithClient): void
    {
        $this->wasAddedWithClient = $wasAddedWithClient;
    }

    public function getCraftsmanId(): ?string
    {
        return $this->craftsmanId;
    }

    public function setCraftsmanId(?string $craftsmanId): void
    {
        $this->craftsmanId = $craftsmanId;
    }

    public function getMap(): ?string
    {
        return $this->map;
    }

    public function setMap(?string $map): void
    {
        $this->map = $map;
    }

    public function getUploadedAt(): ?DateTime
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(?DateTime $uploadedAt): void
    {
        $this->uploadedAt = $uploadedAt;
    }

    public function getUploadByName(): ?string
    {
        return $this->uploadByName;
    }

    public function setUploadByName(?string $uploadByName): void
    {
        $this->uploadByName = $uploadByName;
    }

    public function getImageThumbnail(): ?string
    {
        return $this->imageThumbnail;
    }

    public function setImageThumbnail(?string $imageThumbnail): void
    {
        $this->imageThumbnail = $imageThumbnail;
    }

    public function getImageFull(): ?string
    {
        return $this->imageFull;
    }

    public function setImageFull(?string $imageFull): void
    {
        $this->imageFull = $imageFull;
    }

    public function getResponseLimit(): ?DateTime
    {
        return $this->responseLimit;
    }

    public function setResponseLimit(?DateTime $responseLimit): void
    {
        $this->responseLimit = $responseLimit;
    }
}
