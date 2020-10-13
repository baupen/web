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
use Symfony\Component\Uid\Uuid;

/**
 * A Filter is used to share a selection of issues.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Filter extends BaseEntity
{
    use IdTrait;

    public const STATUS_NEW = 0;
    public const STATUS_REGISTERED = 1;
    public const STATUS_READ = 2;
    public const STATUS_RESPONDED = 4;
    public const STATUS_REVIEWED = 8;

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
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $wasAddedWithClient;

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

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

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
        $this->publicAccessIdentifier = Uuid::v4();
    }

    /**
     * @param string[]|null $issues
     */
    public function filterByIssues(?array $issues): void
    {
        $this->issues = $issues;
        $this->filterByIssues = true;
    }

    public function filterByIsMarked(bool $isMarked): void
    {
        $this->isMarked = $isMarked;
    }

    public function filterByWasAddedWithClient(bool $wasAddedWithClient): void
    {
        $this->wasAddedWithClient = $wasAddedWithClient;
    }

    public function filterByResponseLimitEnd(?DateTime $limitEnd): void
    {
        $this->limitEnd = $limitEnd;
    }

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
     */
    public function filterByRegistrationStatus(bool $registrationStatus, ?DateTime $registrationStart = null, ?DateTime $registrationEnd = null): void
    {
        $this->registrationStatus = $registrationStatus;
        $this->registrationStart = $registrationStart;
        $this->registrationEnd = $registrationEnd;
    }

    /**
     * @param bool|null $respondedStatus
     */
    public function filterByRespondedStatus(bool $respondedStatus, ?DateTime $respondedStart = null, ?DateTime $respondedEnd = null): void
    {
        $this->respondedStatus = $respondedStatus;
        $this->respondedStart = $respondedStart;
        $this->respondedEnd = $respondedEnd;
    }

    /**
     * @param bool|null $reviewedStatus
     */
    public function filterByReviewedStatus(bool $reviewedStatus, ?DateTime $reviewedStart = null, ?DateTime $reviewedEnd = null): void
    {
        $this->reviewedStatus = $reviewedStatus;
        $this->reviewedStart = $reviewedStart;
        $this->reviewedEnd = $reviewedEnd;
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function getPublicAccessIdentifier(): ?string
    {
        return $this->publicAccessIdentifier;
    }

    public function getAccessAllowedUntil(): ?DateTime
    {
        return $this->accessAllowedUntil;
    }

    public function getLastAccess(): ?DateTime
    {
        return $this->lastAccess;
    }

    public function getIsMarked(): ?bool
    {
        return $this->isMarked;
    }

    public function getWasAddedWithClient(): ?bool
    {
        return $this->wasAddedWithClient;
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

    public function getRegistrationStatus(): ?bool
    {
        return $this->registrationStatus;
    }

    public function getRegistrationStart(): ?DateTime
    {
        return $this->registrationStart;
    }

    public function getRegistrationEnd(): ?DateTime
    {
        return $this->registrationEnd;
    }

    public function getRespondedStatus(): ?bool
    {
        return $this->respondedStatus;
    }

    public function getRespondedStart(): ?DateTime
    {
        return $this->respondedStart;
    }

    public function getRespondedEnd(): ?DateTime
    {
        return $this->respondedEnd;
    }

    public function getReviewedStatus(): ?bool
    {
        return $this->reviewedStatus;
    }

    public function getReviewedStart(): ?DateTime
    {
        return $this->reviewedStart;
    }

    public function getReviewedEnd(): ?DateTime
    {
        return $this->reviewedEnd;
    }

    public function getLimitStart(): ?DateTime
    {
        return $this->limitStart;
    }

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

        return null === $this->getAccessAllowedUntil() || $this->getAccessAllowedUntil() > $now;
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
