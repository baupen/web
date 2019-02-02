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
use App\Entity\Traits\TimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An issue is something created by the construction manager to inform the craftsman of it.
 *
 * @ORM\Table(name="issue")
 * @ORM\Entity(repositoryClass="App\Repository\IssueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Issue extends BaseEntity
{
    use IdTrait;
    use TimeTrait;

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
    private $isMarked;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $wasAddedWithClient;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $responseLimit;

    /**
     * @var \DateTime
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
     * @var \DateTime|null
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
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $respondedAt;

    /**
     * @var Craftsman|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Craftsman")
     */
    private $responseBy;

    /**
     * @var \DateTime|null
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
     * @var IssuePosition|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\IssuePosition", mappedBy="issue")
     */
    private $position;

    /**
     * @var IssueImage[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\IssueImage", mappedBy="issue")
     */
    private $images;

    /**
     * @var IssueImage|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\IssueImage")
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

    public function __construct()
    {
        $this->images = new ArrayCollection();
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
     * @return bool
     */
    public function getIsMarked(): bool
    {
        return $this->isMarked;
    }

    /**
     * @param bool $isMarked
     */
    public function setIsMarked(bool $isMarked): void
    {
        $this->isMarked = $isMarked;
    }

    /**
     * @return bool
     */
    public function getWasAddedWithClient(): bool
    {
        return $this->wasAddedWithClient;
    }

    /**
     * @param bool $wasAddedWithClient
     */
    public function setWasAddedWithClient(bool $wasAddedWithClient): void
    {
        $this->wasAddedWithClient = $wasAddedWithClient;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return \DateTime|null
     */
    public function getResponseLimit(): ?\DateTime
    {
        return $this->responseLimit;
    }

    /**
     * @param \DateTime|null $responseLimit
     */
    public function setResponseLimit(?\DateTime $responseLimit): void
    {
        $this->responseLimit = $responseLimit;
    }

    /**
     * @return \DateTime
     */
    public function getUploadedAt(): \DateTime
    {
        return $this->uploadedAt;
    }

    /**
     * @param \DateTime $uploadedAt
     */
    public function setUploadedAt(\DateTime $uploadedAt): void
    {
        $this->uploadedAt = $uploadedAt;
    }

    /**
     * @return ConstructionManager
     */
    public function getUploadBy(): ConstructionManager
    {
        return $this->uploadBy;
    }

    /**
     * @param ConstructionManager $uploadBy
     */
    public function setUploadBy(ConstructionManager $uploadBy): void
    {
        $this->uploadBy = $uploadBy;
    }

    /**
     * @return \DateTime|null
     */
    public function getRegisteredAt(): ?\DateTime
    {
        return $this->registeredAt;
    }

    /**
     * @param \DateTime|null $registeredAt
     */
    public function setRegisteredAt(?\DateTime $registeredAt): void
    {
        $this->registeredAt = $registeredAt;
    }

    /**
     * @return ConstructionManager|null
     */
    public function getRegistrationBy(): ?ConstructionManager
    {
        return $this->registrationBy;
    }

    /**
     * @param ConstructionManager|null $registrationBy
     */
    public function setRegistrationBy(?ConstructionManager $registrationBy): void
    {
        $this->registrationBy = $registrationBy;
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
     * @return Craftsman|null
     */
    public function getResponseBy(): ?Craftsman
    {
        return $this->responseBy;
    }

    /**
     * @param Craftsman|null $responseBy
     */
    public function setResponseBy(?Craftsman $responseBy): void
    {
        $this->responseBy = $responseBy;
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
     * @return ConstructionManager|null
     */
    public function getReviewBy(): ?ConstructionManager
    {
        return $this->reviewBy;
    }

    /**
     * @param ConstructionManager|null $reviewBy
     */
    public function setReviewBy(?ConstructionManager $reviewBy): void
    {
        $this->reviewBy = $reviewBy;
    }

    /**
     * @return Craftsman|null
     */
    public function getCraftsman(): ?Craftsman
    {
        return $this->craftsman;
    }

    /**
     * @param Craftsman|null $craftsman
     */
    public function setCraftsman(?Craftsman $craftsman): void
    {
        $this->craftsman = $craftsman;
    }

    /**
     * @return Map
     */
    public function getMap(): Map
    {
        return $this->map;
    }

    /**
     * @param Map $map
     */
    public function setMap(Map $map): void
    {
        $this->map = $map;
    }

    const UPLOAD_STATUS = 1;
    const REGISTRATION_STATUS = 2;
    const RESPONSE_STATUS = 4;
    const REVIEW_STATUS = 8;

    /**
     * returns a unique code for all possible status.
     *
     * @return int
     */
    public function getStatusCode()
    {
        $res = self::UPLOAD_STATUS;
        if ($this->getRegisteredAt() !== null) {
            $res = $res | self::REGISTRATION_STATUS;
        }
        if ($this->getRespondedAt() !== null) {
            $res = $res | self::RESPONSE_STATUS;
        }
        if ($this->getReviewedAt() !== null) {
            $res = $res | self::REVIEW_STATUS;
        }

        return $res;
    }

    /**
     * @return IssueImage[]|ArrayCollection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @return IssueImage|null
     */
    public function getImage(): ?IssueImage
    {
        return $this->image;
    }

    /**
     * @param IssueImage|null $image
     */
    public function setImage(?IssueImage $image): void
    {
        $this->image = $image;
    }

    /**
     * @return IssuePosition|null
     */
    public function getPosition(): ?IssuePosition
    {
        return $this->position;
    }

    /**
     * @param IssuePosition|null $position
     */
    public function setPosition(?IssuePosition $position): void
    {
        $this->position = $position;
    }
}
