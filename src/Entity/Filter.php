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
use Ramsey\Uuid\Uuid;

/**
 * A Filter is used to share a selection of issues.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Filter extends BaseEntity
{
    use IdTrait;

    const STATUS_REGISTERED = 1;
    const STATUS_READ = 2;
    const STATUS_RESPONDED = 4;
    const STATUS_REVIEWED = 8;

    /**
     * @var ConstructionSite
     *
     * @ORM\ManyToOne(targetEntity="ConstructionSite", inversedBy="filters")
     */
    private $constructionSite;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isMarked = null;

    /**
     * @var string[]
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $trades = null;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $filterByTrades = false;

    /**
     * @var string[]
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $craftsmen = null;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $filterByCraftsmen = false;

    /**
     * @var string[]
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $maps = null;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $filterByMaps = false;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $issues = null;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $filterByIssues = false;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $anyStatus = null;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $registrationStatus = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registrationStart = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registrationEnd = null;

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
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $publicAccessIdentifier;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $accessAllowedUntil = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastAccess = null;

    /**
     * @param string $constructionSite
     */
    public function setConstructionSite(string $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    /**
     * @param \DateTime|null $accessAllowedUntil
     */
    public function setAccessAllowedUntil(?\DateTime $accessAllowedUntil): void
    {
        $this->accessAllowedUntil = $accessAllowedUntil;
    }

    /**
     * sets the last access to now.
     *
     * @throws \Exception
     */
    public function setLastAccessNow(): void
    {
        $this->lastAccess = new \DateTime();
    }

    /**
     * sets a new access identifier for public access.
     *
     * @throws \Exception
     */
    public function setPublicAccessIdentifier(): void
    {
        $this->publicAccessIdentifier = Uuid::uuid4()->toString();
    }

    /**
     * @param string[]|null $issues
     */
    public function filterByIssues(?array $issues): void
    {
        $this->issues = $issues;
        $this->filterByIssues = true;
    }

    /**
     * @param bool $isMarked
     */
    public function filterByIsMarked(bool $isMarked): void
    {
        $this->isMarked = $isMarked;
    }

    /**
     * @param \DateTime|null $limitEnd
     */
    public function filterByResponseLimitEnd(?\DateTime $limitEnd): void
    {
        $this->limitEnd = $limitEnd;
    }

    /**
     * @param int $anyStatus
     */
    public function filterByAnyStatus(int $anyStatus): void
    {
        $this->anyStatus = $anyStatus;
    }

    /**
     * @param string[] $craftsmen
     */
    public function filterByCraftsmen(array $craftsmen): void
    {
        $this->craftsmen = $craftsmen;
        $this->filterByCraftsmen = true;
    }

    /**
     * @param string[] $trades
     */
    public function filterByTrades(array $trades): void
    {
        $this->trades = $trades;
        $this->filterByTrades = true;
    }

    /**
     * @param string[] $maps
     */
    public function filterByMaps(array $maps): void
    {
        $this->maps = $maps;
        $this->filterByMaps = true;
    }

    /**
     * @param bool|null $registrationStatus
     * @param \DateTime|null $registrationStart
     * @param \DateTime|null $registrationEnd
     */
    public function filterByRegistrationStatus(?bool $registrationStatus, ?\DateTime $registrationStart = null, ?\DateTime $registrationEnd = null): void
    {
        $this->registrationStatus = $registrationStatus;
        $this->registrationStart = $registrationStart;
        $this->registrationEnd = $registrationEnd;
    }

    /**
     * @param bool|null $respondedStatus
     * @param \DateTime|null $respondedStart
     * @param \DateTime|null $respondedEnd
     */
    public function filterByRespondedStatus(?bool $respondedStatus, ?\DateTime $respondedStart = null, ?\DateTime $respondedEnd = null): void
    {
        $this->respondedStatus = $respondedStatus;
        $this->respondedStart = $respondedStart;
        $this->respondedEnd = $respondedEnd;
    }

    /**
     * @param bool|null $respondedStatus
     * @param \DateTime|null $reviewedStart
     * @param \DateTime|null $reviewedEnd
     */
    public function filterByReviewedStatus(?bool $respondedStatus, ?\DateTime $reviewedStart = null, ?\DateTime $reviewedEnd = null): void
    {
        $this->respondedStatus = $respondedStatus;
        $this->reviewedStart = $reviewedStart;
        $this->reviewedEnd = $reviewedEnd;
    }
}
