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

class Issue extends \App\Api\Entity\Foyer\Issue
{
    /**
     * @var string
     */
    private $number;

    /**
     * @var \DateTime
     */
    private $registeredAt;

    /**
     * @var string
     */
    private $registrationByName;

    /**
     * @var \DateTime|null
     */
    private $respondedAt;

    /**
     * @var string|null
     */
    private $responseByName;

    /**
     * @var \DateTime|null
     */
    private $reviewedAt;

    /**
     * @var string|null
     */
    private $reviewByName;

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

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
     * @return \DateTime|null
     */
    public function getRespondedAt(): ?\DateTime
    {
        return $this->respondedAt;
    }

    /**
     * @param \DateTime|null $respondedAt
     */
    public function setRespondedAt(?\DateTime $respondedAt): void
    {
        $this->respondedAt = $respondedAt;
    }

    /**
     * @return null|string
     */
    public function getResponseByName(): ?string
    {
        return $this->responseByName;
    }

    /**
     * @param null|string $responseByName
     */
    public function setResponseByName(?string $responseByName): void
    {
        $this->responseByName = $responseByName;
    }

    /**
     * @return \DateTime|null
     */
    public function getReviewedAt(): ?\DateTime
    {
        return $this->reviewedAt;
    }

    /**
     * @param \DateTime|null $reviewedAt
     */
    public function setReviewedAt(?\DateTime $reviewedAt): void
    {
        $this->reviewedAt = $reviewedAt;
    }

    /**
     * @return null|string
     */
    public function getReviewByName(): ?string
    {
        return $this->reviewByName;
    }

    /**
     * @param null|string $reviewByName
     */
    public function setReviewByName(?string $reviewByName): void
    {
        $this->reviewByName = $reviewByName;
    }
}
