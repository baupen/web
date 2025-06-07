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
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Filter extends BaseEntity implements ConstructionSiteOwnedEntityInterface
{
    use IdTrait;
    use AuthenticationTrait;
    use TimeTrait;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN, nullable: true)]
    private ?bool $isDeleted = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN, nullable: true)]
    private ?bool $isMarked = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN, nullable: true)]
    private ?bool $wasAddedWithClient = null;

    /**
     * @var string[]|null
     */
    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $numbers = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER, nullable: true)]
    private ?int $state = null;

    /**
     * @var string[]|null
     */
    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $craftsmanIds = null;

    /**
     * @var string[]|null
     */
    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $mapIds = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $deadlineBefore = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $deadlineAfter = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, nullable: true)]
    private ?string $createdBy = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, nullable: true)]
    private ?string $registeredBy = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, nullable: true)]
    private ?string $closedBy = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $createdAtAfter = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $createdAtBefore = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $registeredAtAfter = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $registeredAtBefore = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $resolvedAtAfter = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $resolvedAtBefore = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $closedAtAfter = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $closedAtBefore = null;

    #[Groups(['filter-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $accessAllowedBefore = null;

    #[Groups(['filter-read', 'filter-create'])]
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: ConstructionSite::class, inversedBy: 'filters')]
    private ?ConstructionSite $constructionSite = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $lastUsedAt = null;

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
        return null === $this->numbers || [] === $this->numbers ? null : $this->numbers;
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
        return null === $this->craftsmanIds || [] === $this->craftsmanIds ? null : $this->craftsmanIds;
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
        return null === $this->mapIds || [] === $this->mapIds ? null : $this->mapIds;
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

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getRegisteredBy(): ?string
    {
        return $this->registeredBy;
    }

    public function setRegisteredBy(?string $registeredBy): void
    {
        $this->registeredBy = $registeredBy;
    }

    public function getClosedBy(): ?string
    {
        return $this->closedBy;
    }

    public function setClosedBy(?string $closedBy): void
    {
        $this->closedBy = $closedBy;
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
