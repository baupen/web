<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Filter is used to share a selection of issues.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Filter extends BaseEntity
{
    use IdTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $constructionSite;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isMarked = null;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $number = null;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $trades = null;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $craftsmen = null;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $maps = null;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $registrationStatus = null;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $respondedStatus = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $respondedStart = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $respondedEnd = null;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $reviewedStatus = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $reviewedStart = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $reviewedEnd = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $limitStart = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $limitEnd = null;

    /**
     * @return string
     */
    public function getConstructionSite(): string
    {
        return $this->constructionSite;
    }

    /**
     * @param string $constructionSite
     */
    public function setConstructionSite(string $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    /**
     * @return bool|null
     */
    public function getIsMarked(): ?bool
    {
        return $this->isMarked;
    }

    /**
     * @param bool|null $isMarked
     */
    public function setIsMarked(?bool $isMarked): void
    {
        $this->isMarked = $isMarked;
    }

    /**
     * @return int|null
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @param int|null $number
     */
    public function setNumber(?int $number): void
    {
        $this->number = $number;
    }

    /**
     * @return null|string[]
     */
    public function getTrades(): ?array
    {
        return $this->trades;
    }

    /**
     * @param null|string[] $trades
     */
    public function setTrades(?array $trades): void
    {
        $this->trades = $trades;
    }

    /**
     * @return null|string[]
     */
    public function getCraftsmen(): ?array
    {
        return $this->craftsmen;
    }

    /**
     * @param null|string[] $craftsmen
     */
    public function setCraftsmen(?array $craftsmen): void
    {
        $this->craftsmen = $craftsmen;
    }

    /**
     * @return null|string[]
     */
    public function getMaps(): ?array
    {
        return $this->maps;
    }

    /**
     * @param null|string[] $maps
     */
    public function setMaps(?array $maps): void
    {
        $this->maps = $maps;
    }

    /**
     * @return bool|null
     */
    public function getRegistrationStatus(): ?bool
    {
        return $this->registrationStatus;
    }

    /**
     * @param bool|null $registrationStatus
     */
    public function setRegistrationStatus(?bool $registrationStatus): void
    {
        $this->registrationStatus = $registrationStatus;
    }

    /**
     * @return bool|null
     */
    public function getRespondedStatus(): ?bool
    {
        return $this->respondedStatus;
    }

    /**
     * @param bool|null $respondedStatus
     */
    public function setRespondedStatus(?bool $respondedStatus): void
    {
        $this->respondedStatus = $respondedStatus;
    }

    /**
     * @return \DateTime|null
     */
    public function getRespondedStart(): ?\DateTime
    {
        return $this->respondedStart;
    }

    /**
     * @param \DateTime|null $respondedStart
     */
    public function setRespondedStart(?\DateTime $respondedStart): void
    {
        $this->respondedStart = $respondedStart;
    }

    /**
     * @return \DateTime|null
     */
    public function getRespondedEnd(): ?\DateTime
    {
        return $this->respondedEnd;
    }

    /**
     * @param \DateTime|null $respondedEnd
     */
    public function setRespondedEnd(?\DateTime $respondedEnd): void
    {
        $this->respondedEnd = $respondedEnd;
    }

    /**
     * @return bool|null
     */
    public function getReviewedStatus(): ?bool
    {
        return $this->reviewedStatus;
    }

    /**
     * @param bool|null $reviewedStatus
     */
    public function setReviewedStatus(?bool $reviewedStatus): void
    {
        $this->reviewedStatus = $reviewedStatus;
    }

    /**
     * @return \DateTime|null
     */
    public function getReviewedStart(): ?\DateTime
    {
        return $this->reviewedStart;
    }

    /**
     * @param \DateTime|null $reviewedStart
     */
    public function setReviewedStart(?\DateTime $reviewedStart): void
    {
        $this->reviewedStart = $reviewedStart;
    }

    /**
     * @return \DateTime|null
     */
    public function getReviewedEnd(): ?\DateTime
    {
        return $this->reviewedEnd;
    }

    /**
     * @param \DateTime|null $reviewedEnd
     */
    public function setReviewedEnd(?\DateTime $reviewedEnd): void
    {
        $this->reviewedEnd = $reviewedEnd;
    }

    /**
     * @return \DateTime|null
     */
    public function getLimitStart(): ?\DateTime
    {
        return $this->limitStart;
    }

    /**
     * @param \DateTime|null $limitStart
     */
    public function setLimitStart(?\DateTime $limitStart): void
    {
        $this->limitStart = $limitStart;
    }

    /**
     * @return \DateTime|null
     */
    public function getLimitEnd(): ?\DateTime
    {
        return $this->limitEnd;
    }

    /**
     * @param \DateTime|null $limitEnd
     */
    public function setLimitEnd(?\DateTime $limitEnd): void
    {
        $this->limitEnd = $limitEnd;
    }
}
