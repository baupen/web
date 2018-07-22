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

class PublicIssue extends Issue
{
    /**
     * @var \DateTime
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

    /**
     * @return \DateTime
     */
    public function getRegisteredAt(): \DateTime
    {
        return $this->registeredAt;
    }

    /**
     * @param \DateTime $registeredAt
     */
    public function setRegisteredAt(\DateTime $registeredAt): void
    {
        $this->registeredAt = $registeredAt;
    }

    /**
     * @return string
     */
    public function getRegistrationByName(): string
    {
        return $this->registrationByName;
    }

    /**
     * @param string $registrationByName
     */
    public function setRegistrationByName(string $registrationByName): void
    {
        $this->registrationByName = $registrationByName;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    /**
     * @return null|string
     */
    public function getImageShareView(): ?string
    {
        return $this->imageShareView;
    }

    /**
     * @param null|string $imageShareView
     */
    public function setImageShareView(?string $imageShareView): void
    {
        $this->imageShareView = $imageShareView;
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
