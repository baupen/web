<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Register;

use DateTime;

class Issue extends \App\Api\Entity\Foyer\Issue
{
    /**
     * @var string
     */
    private $number;

    /**
     * @var DateTime
     */
    private $registeredAt;

    /**
     * @var string
     */
    private $registrationByName;

    /**
     * @var DateTime|null
     */
    private $respondedAt;

    /**
     * @var string|null
     */
    private $responseByName;

    /**
     * @var DateTime|null
     */
    private $reviewedAt;

    /**
     * @var string|null
     */
    private $reviewByName;

    /**
     * @var bool
     */
    private $isRead;

    /**
     * @var bool
     */
    private $wasAddedWithClient;

    /**
     * @var string
     */
    private $mapId;

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    public function getRegisteredAt(): DateTime
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(DateTime $registeredAt): void
    {
        $this->registeredAt = $registeredAt;
    }

    public function getRegistrationByName(): string
    {
        return $this->registrationByName;
    }

    public function setRegistrationByName(string $registrationByName): void
    {
        $this->registrationByName = $registrationByName;
    }

    public function getRespondedAt(): ?DateTime
    {
        return $this->respondedAt;
    }

    public function setRespondedAt(?DateTime $respondedAt): void
    {
        $this->respondedAt = $respondedAt;
    }

    public function getResponseByName(): ?string
    {
        return $this->responseByName;
    }

    public function setResponseByName(?string $responseByName): void
    {
        $this->responseByName = $responseByName;
    }

    public function getReviewedAt(): ?DateTime
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(?DateTime $reviewedAt): void
    {
        $this->reviewedAt = $reviewedAt;
    }

    public function getReviewByName(): ?string
    {
        return $this->reviewByName;
    }

    public function setReviewByName(?string $reviewByName): void
    {
        $this->reviewByName = $reviewByName;
    }

    public function getIsRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): void
    {
        $this->isRead = $isRead;
    }

    public function getMapId(): string
    {
        return $this->mapId;
    }

    public function setMapId(string $mapId): void
    {
        $this->mapId = $mapId;
    }

    public function getWasAddedWithClient(): bool
    {
        return $this->wasAddedWithClient;
    }

    public function setWasAddedWithClient(bool $wasAddedWithClient): void
    {
        $this->wasAddedWithClient = $wasAddedWithClient;
    }
}
