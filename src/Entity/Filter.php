<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Traits\AuthenticationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Filter is used to share a selection of issues.
 *
 * @ApiResource(
 *     collectionOperations={
 *      "post" = {"security_post_denormalize" = "is_granted('FILTER_CREATE', object)", "denormalization_context"={"groups"={"filter-create"}}}
 *      },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('FILTER_VIEW', object)"}
 *     },
 *     normalizationContext={"groups"={"filter-read"}, "skip_null_values"=false},
 *     denormalizationContext={"groups"={"filter-write"}},
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\IssueRepository")
 *
 * @ORM\HasLifecycleCallbacks
 */
class Filter extends BaseEntity implements ConstructionSiteOwnedEntityInterface
{
    use IdTrait;
    use AuthenticationTrait;
    use TimeTrait;

    /**
     * @var bool|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDeleted;

    /**
     * @var bool|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isMarked;

    /**
     * @var bool|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $wasAddedWithClient;

    /**
     * @var string[]|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $numbers;

    /**
     * @var string|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var int|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $state;

    /**
     * @var string[]|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $craftsmanIds;

    /**
     * @var string[]|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $mapIds;

    /**
     * @var \DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deadlineBefore;

    /**
     * @var \DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deadlineAfter;

    /**
     * @var \DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAtAfter;

    /**
     * @var \DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAtBefore;

    /**
     * @var \DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registeredAtAfter;

    /**
     * @var \DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registeredAtBefore;

    /**
     * @var \DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resolvedAtAfter;

    /**
     * @var \DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resolvedAtBefore;

    /**
     * @var \DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closedAtAfter;

    /**
     * @var \DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closedAtBefore;

    /**
     * @var \DateTime|null
     *
     * @Groups({"filter-create"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $accessAllowedBefore;

    /**
     * @var ConstructionSite
     *
     * @Groups({"filter-read", "filter-create"})
     *
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne(targetEntity="ConstructionSite", inversedBy="filters")
     */
    private $constructionSite;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastUsedAt;

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): void
    {
        $this->isDeleted = $isDeleted;
    }

    public function getIsMarked(): ?bool
    {
        return $this->isMarked;
    }

    public function setIsMarked(?bool $isMarked): void
    {
        $this->isMarked = $isMarked;
    }

    public function getWasAddedWithClient(): ?bool
    {
        return $this->wasAddedWithClient;
    }

    public function setWasAddedWithClient(?bool $wasAddedWithClient): void
    {
        $this->wasAddedWithClient = $wasAddedWithClient;
    }

    /**
     * @return string[]|null
     */
    public function getNumbers(): ?array
    {
        return !empty($this->numbers) ? $this->numbers : null;
    }

    /**
     * @param string[]|null $numbers
     */
    public function setNumbers(?array $numbers): void
    {
        $this->numbers = $numbers;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(?int $state): void
    {
        $this->state = $state;
    }

    /**
     * @return string[]|null
     */
    public function getCraftsmanIds(): ?array
    {
        return !empty($this->craftsmanIds) ? $this->craftsmanIds : null;
    }

    /**
     * @param string[]|null $craftsmanIds
     */
    public function setCraftsmanIds(?array $craftsmanIds): void
    {
        $this->craftsmanIds = $craftsmanIds;
    }

    /**
     * @return string[]|null
     */
    public function getMapIds(): ?array
    {
        return !empty($this->mapIds) ? $this->mapIds : null;
    }

    /**
     * @param string[]|null $mapIds
     */
    public function setMapIds(?array $mapIds): void
    {
        $this->mapIds = $mapIds;
    }

    public function getDeadlineBefore(): ?\DateTime
    {
        return $this->deadlineBefore;
    }

    public function setDeadlineBefore(?\DateTime $deadlineBefore): void
    {
        $this->deadlineBefore = $deadlineBefore;
    }

    public function getDeadlineAfter(): ?\DateTime
    {
        return $this->deadlineAfter;
    }

    public function setDeadlineAfter(?\DateTime $deadlineAfter): void
    {
        $this->deadlineAfter = $deadlineAfter;
    }

    public function getCreatedAtAfter(): ?\DateTime
    {
        return $this->createdAtAfter;
    }

    public function setCreatedAtAfter(?\DateTime $createdAtAfter): void
    {
        $this->createdAtAfter = $createdAtAfter;
    }

    public function getCreatedAtBefore(): ?\DateTime
    {
        return $this->createdAtBefore;
    }

    public function setCreatedAtBefore(?\DateTime $createdAtBefore): void
    {
        $this->createdAtBefore = $createdAtBefore;
    }

    public function getRegisteredAtAfter(): ?\DateTime
    {
        return $this->registeredAtAfter;
    }

    public function setRegisteredAtAfter(?\DateTime $registeredAtAfter): void
    {
        $this->registeredAtAfter = $registeredAtAfter;
    }

    public function getRegisteredAtBefore(): ?\DateTime
    {
        return $this->registeredAtBefore;
    }

    public function setRegisteredAtBefore(?\DateTime $registeredAtBefore): void
    {
        $this->registeredAtBefore = $registeredAtBefore;
    }

    public function getResolvedAtAfter(): ?\DateTime
    {
        return $this->resolvedAtAfter;
    }

    public function setResolvedAtAfter(?\DateTime $resolvedAtAfter): void
    {
        $this->resolvedAtAfter = $resolvedAtAfter;
    }

    public function getResolvedAtBefore(): ?\DateTime
    {
        return $this->resolvedAtBefore;
    }

    public function setResolvedAtBefore(?\DateTime $resolvedAtBefore): void
    {
        $this->resolvedAtBefore = $resolvedAtBefore;
    }

    public function getClosedAtAfter(): ?\DateTime
    {
        return $this->closedAtAfter;
    }

    public function setClosedAtAfter(?\DateTime $closedAtAfter): void
    {
        $this->closedAtAfter = $closedAtAfter;
    }

    public function getClosedAtBefore(): ?\DateTime
    {
        return $this->closedAtBefore;
    }

    public function setClosedAtBefore(?\DateTime $closedAtBefore): void
    {
        $this->closedAtBefore = $closedAtBefore;
    }

    public function getAccessAllowedBefore(): ?\DateTime
    {
        return $this->accessAllowedBefore;
    }

    public function setAccessAllowedBefore(?\DateTime $accessAllowedBefore): void
    {
        $this->accessAllowedBefore = $accessAllowedBefore;
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

    public function getLastUsedAt(): ?\DateTime
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(): void
    {
        $this->lastUsedAt = new \DateTime();
    }
}
