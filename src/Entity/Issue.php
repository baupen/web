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
use Doctrine\ORM\Mapping as ORM;

/**
 * An issue is something created by the construction manager to inform the craftsman of it.
 *
 * @ORM\Table(name="issue")
 * @ORM\Entity
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
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $imageFilename;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $answerLimit;

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
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return null|string
     */
    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    /**
     * @param null|string $imageFilename
     */
    public function setImageFilename(?string $imageFilename): void
    {
        $this->imageFilename = $imageFilename;
    }

    /**
     * @return \DateTime|null
     */
    public function getAnswerLimit(): ?\DateTime
    {
        return $this->answerLimit;
    }

    /**
     * @param \DateTime|null $answerLimit
     */
    public function setAnswerLimit(?\DateTime $answerLimit): void
    {
        $this->answerLimit = $answerLimit;
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
     * @return float|null
     */
    public function getPositionX(): ?float
    {
        return $this->positionX;
    }

    /**
     * @param float|null $positionX
     */
    public function setPositionX(?float $positionX): void
    {
        $this->positionX = $positionX;
    }

    /**
     * @return float|null
     */
    public function getPositionY(): ?float
    {
        return $this->positionY;
    }

    /**
     * @param float|null $positionY
     */
    public function setPositionY(?float $positionY): void
    {
        $this->positionY = $positionY;
    }

    /**
     * @return float|null
     */
    public function getPositionZoomScale(): ?float
    {
        return $this->positionZoomScale;
    }

    /**
     * @param float|null $positionZoomScale
     */
    public function setPositionZoomScale(?float $positionZoomScale): void
    {
        $this->positionZoomScale = $positionZoomScale;
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

    /**
     * @return null|string
     */
    public function getImageFilePath(): ?string
    {
        if (null !== $this->getImageFilename()) {
            return 'upload/' . $this->getMap()->getConstructionSite()->getId() . '/issue/' . $this->getImageFilename();
        }

        return null;
    }
}
