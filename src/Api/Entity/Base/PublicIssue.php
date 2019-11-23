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

use DateTime;

class PublicIssue extends Issue
{
    /**
     * @var DateTime
     */
    private $registeredAt;

    /**
     * @var string
     */
    private $registrationByName;

    /**
     * @var int
     */
    private $number;

    /**
     * @var string|null
     */
    private $imageShareView;

    /**
     * @var string|null
     */
    private $imageFull;

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

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    public function getImageShareView(): ?string
    {
        return $this->imageShareView;
    }

    public function setImageShareView(?string $imageShareView): void
    {
        $this->imageShareView = $imageShareView;
    }

    public function getImageFull(): ?string
    {
        return $this->imageFull;
    }

    public function setImageFull(?string $imageFull): void
    {
        $this->imageFull = $imageFull;
    }
}
