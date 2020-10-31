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

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use App\Api\Filters\IsDeletedFilter;
use App\Api\Filters\RequiredSearchFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Issue\IssuePositionTrait;
use App\Entity\Issue\IssueStatusTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An issue is something created by the construction manager to inform the craftsman of it.
 *
 * @ApiResource(
 *     collectionOperations={
 *      "get",
 *      "post" = {"security_post_denormalize" = "is_granted('ISSUE_MODIFY', object)", "denormalization_context"={"groups"={"issue-create", "issue-write"}}}
 *      },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('ISSUE_VIEW', object)"},
 *      "patch" = {"security" = "is_granted('ISSUE_MODIFY', object)"},
 *      "delete" = {"security" = "is_granted('ISSUE_MODIFY', object)"},
 *     },
 *     normalizationContext={"groups"={"issue-read"}, "skip_null_values"=false},
 *     denormalizationContext={"groups"={"issue-write"}}
 * )
 * @ApiFilter(RequiredSearchFilter::class, properties={"constructionSite"})
 * @ApiFilter(IsDeletedFilter::class, properties={"isDeleted"})
 * @ApiFilter(DateFilter::class, properties={"lastChangedAt"})
 *
 * @ORM\Entity(repositoryClass="App\Repository\IssueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Issue extends BaseEntity
{
    use IdTrait;
    use SoftDeleteTrait;

    use IssuePositionTrait;
    use IssueStatusTrait;

    public const UPLOAD_STATUS = 1;
    public const REGISTRATION_STATUS = 2;
    public const RESPONSE_STATUS = 4;
    public const REVIEW_STATUS = 8;

    /**
     * @var int
     *
     * @Groups({"issue-read"})
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @var bool
     *
     * @Assert\NotBlank
     * @Groups({"issue-read", "issue-write"})
     * @ORM\Column(type="boolean")
     */
    private $isMarked = false;

    /**
     * @var bool
     *
     * @Assert\NotBlank
     * @Groups({"issue-read", "issue-write"})
     * @ORM\Column(type="boolean")
     */
    private $wasAddedWithClient = false;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(groups={"after-register"})
     * @Groups({"issue-read", "issue-write"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var DateTime|null
     *
     * @Groups({"issue-read", "issue-write"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $responseLimit;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $lastChangedAt;

    /**
     * @var IssueImage|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\IssueImage", mappedBy="issue", cascade={"persist"})
     */
    private $image;

    /**
     * @var Craftsman|null
     *
     * @Groups({"issue-read", "issue-write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Craftsman", inversedBy="issues")
     */
    private $craftsman;

    /**
     * @var Map
     *
     * @Groups({"issue-read", "issue-create"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Map", inversedBy="issues")
     */
    private $map;

    /**
     * @var MapFile|null
     *
     * @Groups({"issue-read", "issue-create"})
     * @ORM\ManyToOne(targetEntity="App\Entity\MapFile", inversedBy="issues")
     */
    private $mapFile;

    /**
     * @var ConstructionSite
     *
     * @Groups({"issue-read", "issue-create"})
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionSite", inversedBy="issues")
     */
    private $constructionSite;

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function prePersistTime()
    {
        $this->lastChangedAt = new DateTime();
    }

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

    /**
     * @Groups({"issue-read"})
     */
    public function getIsDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    /**
     * @Groups({"issue-read"})
     */
    public function getLastChangedAt(): \DateTime
    {
        return $this->lastChangedAt;
    }
}
