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
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
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
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $publicAccessIdentifier;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $accessAllowedUntil;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastAccess;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isMarked;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $issues;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $anyStatus;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $filterByIssues = false;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $craftsmen;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $filterByCraftsmen = false;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $trades;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $filterByTrades = false;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $maps;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $filterByMaps = false;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $registrationStatus;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registrationStart;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registrationEnd;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $respondedStatus;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $respondedStart;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $respondedEnd;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $reviewedStatus;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $reviewedStart;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $reviewedEnd;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $limitStart;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $limitEnd;

    /**
     * @param ConstructionSite $constructionSite
     */
    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    /**
     * @param DateTime|null $accessAllowedUntil
     */
    public function setAccessAllowedUntil(?DateTime $accessAllowedUntil): void
    {
        $this->accessAllowedUntil = $accessAllowedUntil;
    }

    /**
     * sets the last access to now.
     *
     * @throws Exception
     */
    public function setLastAccessNow(): void
    {
        $this->lastAccess = new DateTime();
    }

    /**
     * sets a new access identifier for public access.
     *
     * @throws Exception
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
     * @param DateTime|null $limitEnd
     */
    public function filterByResponseLimitEnd(?DateTime $limitEnd): void
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
     * @param bool|null     $registrationStatus
     * @param DateTime|null $registrationStart
     * @param DateTime|null $registrationEnd
     */
    public function filterByRegistrationStatus(bool $registrationStatus, ?DateTime $registrationStart = null, ?DateTime $registrationEnd = null): void
    {
        $this->registrationStatus = $registrationStatus;
        $this->registrationStart = $registrationStart;
        $this->registrationEnd = $registrationEnd;
    }

    /**
     * @param bool|null     $respondedStatus
     * @param DateTime|null $respondedStart
     * @param DateTime|null $respondedEnd
     */
    public function filterByRespondedStatus(bool $respondedStatus, ?DateTime $respondedStart = null, ?DateTime $respondedEnd = null): void
    {
        $this->respondedStatus = $respondedStatus;
        $this->respondedStart = $respondedStart;
        $this->respondedEnd = $respondedEnd;
    }

    /**
     * @param bool|null     $reviewedStatus
     * @param DateTime|null $reviewedStart
     * @param DateTime|null $reviewedEnd
     */
    public function filterByReviewedStatus(bool $reviewedStatus, ?DateTime $reviewedStart = null, ?DateTime $reviewedEnd = null): void
    {
        $this->reviewedStatus = $reviewedStatus;
        $this->reviewedStart = $reviewedStart;
        $this->reviewedEnd = $reviewedEnd;
    }

    /**
     * @return ConstructionSite
     */
    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    /**
     * @return string|null
     */
    public function getPublicAccessIdentifier(): ?string
    {
        return $this->publicAccessIdentifier;
    }

    /**
     * @return DateTime|null
     */
    public function getAccessAllowedUntil(): ?DateTime
    {
        return $this->accessAllowedUntil;
    }

    /**
     * @return DateTime|null
     */
    public function getLastAccess(): ?DateTime
    {
        return $this->lastAccess;
    }

    /**
     * @return bool|null
     */
    public function getIsMarked(): ?bool
    {
        return $this->isMarked;
    }

    /**
     * @return string[]|null
     */
    public function getIssues(): ?array
    {
        if (!$this->filterByIssues) {
            return null;
        }

        return self::getValidArray($this->issues);
    }

    /**
     * @return int|null
     */
    public function getAnyStatus(): ?int
    {
        return $this->anyStatus;
    }

    /**
     * @return string[]
     */
    public function getCraftsmen(): ?array
    {
        if (!$this->filterByCraftsmen) {
            return null;
        }

        return self::getValidArray($this->craftsmen);
    }

    /**
     * @return string[]|null
     */
    public function getTrades(): ?array
    {
        if (!$this->trades) {
            return null;
        }

        return self::getValidArray($this->trades);
    }

    /**
     * @return string[]|null
     */
    public function getMaps(): ?array
    {
        if (!$this->maps) {
            return null;
        }

        return self::getValidArray($this->maps);
    }

    /**
     * @return bool|null
     */
    public function getRegistrationStatus(): ?bool
    {
        return $this->registrationStatus;
    }

    /**
     * @return DateTime|null
     */
    public function getRegistrationStart(): ?DateTime
    {
        return $this->registrationStart;
    }

    /**
     * @return DateTime|null
     */
    public function getRegistrationEnd(): ?DateTime
    {
        return $this->registrationEnd;
    }

    /**
     * @return bool|null
     */
    public function getRespondedStatus(): ?bool
    {
        return $this->respondedStatus;
    }

    /**
     * @return DateTime|null
     */
    public function getRespondedStart(): ?DateTime
    {
        return $this->respondedStart;
    }

    /**
     * @return DateTime|null
     */
    public function getRespondedEnd(): ?DateTime
    {
        return $this->respondedEnd;
    }

    /**
     * @return bool|null
     */
    public function getReviewedStatus(): ?bool
    {
        return $this->reviewedStatus;
    }

    /**
     * @return DateTime|null
     */
    public function getReviewedStart(): ?DateTime
    {
        return $this->reviewedStart;
    }

    /**
     * @return DateTime|null
     */
    public function getReviewedEnd(): ?DateTime
    {
        return $this->reviewedEnd;
    }

    /**
     * @return DateTime|null
     */
    public function getLimitStart(): ?DateTime
    {
        return $this->limitStart;
    }

    /**
     * @return DateTime|null
     */
    public function getLimitEnd(): ?DateTime
    {
        return $this->limitEnd;
    }

    /**
     * @throws Exception
     *
     * @return bool
     */
    public function isValid()
    {
        $now = new \DateTime();

        return $this->getAccessAllowedUntil() === null || $this->getAccessAllowedUntil() > $now;
    }

    /**
     * @param $array
     *
     * @return array
     */
    private static function getValidArray($array)
    {
        // due to a bug in doctrine empty arrays are saved as null in the db
        // therefore need to handle null arrays as empty arrays
        // bug fix will only be included in 3.0 because it is a breaking change
        return \is_array($array) ? $array : [];
    }
}
