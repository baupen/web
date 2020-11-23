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
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filters\IsDeletedFilter;
use App\Api\Filters\RequiredSearchFilter;
use App\Api\Filters\StateFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
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
 *      "post" = {"security_post_denormalize" = "is_granted('ISSUE_MODIFY', object)", "denormalization_context"={"groups"={"issue-create", "issue-write"}}},
 *      "get_report"={
 *          "method"="GET",
 *          "path"="/issues/report"
 *      }
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
 * @ApiFilter(DateFilter::class, properties={"lastChangedAt", "createdAt", "registeredAt", "resolvedAt", "closedAt", "deadline"})
 * @ApiFilter(BooleanFilter::class, properties={"isMarked", "wasAddedWithClient"})
 * @ApiFilter(NumericFilter::class, properties={"number"})
 * @ApiFilter(SearchFilter::class, properties={"craftsman": "exact", "map": "exact", "description": "partial"})
 * @ApiFilter(StateFilter::class, properties={"state"})
 *
 * @ApiFilter(OrderFilter::class, properties={"lastChangedAt": "ASC"})
 *
 * @ORM\Entity(repositoryClass="App\Repository\IssueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Issue extends BaseEntity implements ConstructionSiteOwnedEntityInterface
{
    use IdTrait;
    use SoftDeleteTrait;

    use IssuePositionTrait;
    use IssueStatusTrait;

    public const STATE_CREATED = 0;
    public const STATE_REGISTERED = 1;
    public const STATE_SEEN = 2;
    public const STATE_RESOLVED = 4;
    public const STATE_CLOSED = 8;

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
     * @Groups({"issue-read", "issue-write"})
     * @ORM\Column(type="boolean")
     */
    private $isMarked = false;

    /**
     * @var bool
     *
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
    private $deadline;

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
     * @Assert\NotBlank(groups={"after-register"})
     * @Groups({"issue-read", "issue-write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Craftsman", inversedBy="issues")
     */
    private $craftsman;

    /**
     * @var Map
     *
     * @Assert\NotBlank()
     * @Groups({"issue-read", "issue-create"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Map", inversedBy="issues")
     */
    private $map;

    /**
     * @var MapFile|null
     *
     * @Assert\NotBlank(groups={"position"})
     * @Groups({"issue-read", "issue-create"})
     * @ORM\ManyToOne(targetEntity="App\Entity\MapFile", inversedBy="issues")
     */
    private $mapFile;

    /**
     * @var ConstructionSite
     *
     * @Assert\NotBlank()
     * @Groups({"issue-create"})
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

    public function getDeadline(): ?DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(?DateTime $deadline): void
    {
        $this->deadline = $deadline;
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
