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
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\TimeTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * An issue is something created by the construction manager to inform the craftsman of it.
 *
 * @ORM\Entity(repositoryClass="App\Repository\IssueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Issue extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use SoftDeleteTrait;

    public const UPLOAD_STATUS = 1;
    public const REGISTRATION_STATUS = 2;
    public const RESPONSE_STATUS = 4;
    public const REVIEW_STATUS = 8;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $number;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isMarked = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $wasAddedWithClient = false;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $responseLimit;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $positionX;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $positionY;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $positionZoomScale;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $uploadedAt;

    /**
     * @var ConstructionManager
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $uploadBy;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registeredAt;

    /**
     * @var ConstructionManager|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $registrationBy;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $respondedAt;

    /**
     * @var Craftsman|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Craftsman", inversedBy="respondedIssues")
     */
    private $responseBy;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $reviewedAt;

    /**
     * @var ConstructionManager|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $reviewBy;

    /**
     * @var IssueImage|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\IssueImage", mappedBy="issue", cascade={"persist"})
     */
    private $image;

    /**
     * @var Craftsman|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Craftsman", inversedBy="issues")
     */
    private $craftsman;

    /**
     * @var Map
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Map", inversedBy="issues")
     */
    private $map;

    /**
     * @var MapFile|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MapFile", inversedBy="issues")
     */
    private $mapFile;

    /**
     * @var ConstructionSite
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionSite", inversedBy="issues")
     */
    private $constructionSite;

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): void
    {
        $this->number = $number;
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getResponseLimit(): ?DateTime
    {
        return $this->responseLimit;
    }

    public function setResponseLimit(?DateTime $responseLimit): void
    {
        $this->responseLimit = $responseLimit;
    }

    public function getUploadedAt(): DateTime
    {
        return $this->uploadedAt;
    }

    public function getUploadBy(): ConstructionManager
    {
        return $this->uploadBy;
    }

    public function uploadedEvent(ConstructionManager $constructionManager)
    {
        $this->uploadBy = $constructionManager;
        $this->uploadedAt = new \DateTime();
    }

    public function getRegisteredAt(): ?DateTime
    {
        return $this->registeredAt;
    }

    public function getRegistrationBy(): ?ConstructionManager
    {
        return $this->registrationBy;
    }

    public function registerEvent(ConstructionManager $constructionManager)
    {
        $this->registrationBy = $constructionManager;
        $this->registeredAt = new \DateTime();
    }

    public function getRespondedAt(): ?DateTime
    {
        return $this->respondedAt;
    }

    public function getResponseBy(): ?Craftsman
    {
        return $this->responseBy;
    }

    public function responseEvent(Craftsman $craftsman)
    {
        $this->responseBy = $craftsman;
        $this->respondedAt = new \DateTime();
    }

    public function getReviewedAt(): ?DateTime
    {
        return $this->reviewedAt;
    }

    public function getReviewBy(): ?ConstructionManager
    {
        return $this->reviewBy;
    }

    public function reviewEvent(ConstructionManager $constructionManager)
    {
        $this->reviewBy = $constructionManager;
        $this->reviewedAt = new \DateTime();
    }

    public function getCraftsman(): ?Craftsman
    {
        return $this->craftsman;
    }

    public function setCraftsman(?Craftsman $craftsman): void
    {
        $this->craftsman = $craftsman;
    }

    public function getMap(): Map
    {
        return $this->map;
    }

    public function setMap(Map $map): void
    {
        $this->map = $map;
    }

    /**
     * returns a unique code for all possible status.
     *
     * @return int
     */
    public function getStatusCode()
    {
        $res = self::UPLOAD_STATUS;
        if (null !== $this->getRegisteredAt()) {
            $res = $res | self::REGISTRATION_STATUS;
        }
        if (null !== $this->getRespondedAt()) {
            $res = $res | self::RESPONSE_STATUS;
        }
        if (null !== $this->getReviewedAt()) {
            $res = $res | self::REVIEW_STATUS;
        }

        return $res;
    }

    public function getImage(): ?IssueImage
    {
        return $this->image;
    }

    public function setImage(?IssueImage $image): void
    {
        $this->image = $image;
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    public function isConstructionSiteSet(): bool
    {
        return null !== $this->constructionSite;
    }

    public function getMapFile(): ?MapFile
    {
        return $this->mapFile;
    }

    public function setMapFile(?MapFile $mapFile): void
    {
        $this->mapFile = $mapFile;
    }

    public function getPositionX(): ?float
    {
        return $this->positionX;
    }

    public function getPositionY(): ?float
    {
        return $this->positionY;
    }

    public function getPositionZoomScale(): ?float
    {
        return $this->positionZoomScale;
    }
}
