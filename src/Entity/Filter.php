<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\IriFilter;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Api\Provider\AuthenticatedConstructionSiteProvider;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Traits\AuthenticationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Filter is used to share a selection of issues.
 *
 * @ApiResource(
 *     collectionOperations={
 *      "post" = {"security_post_denormalize" = "is_granted('FILTER_CREATE', object)", "denormalization_context"={"groups"={"filter:create"}}}
 *      },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('FILTER_VIEW', object)"}
 *     },
 *     normalizationContext={"groups"={"filter:read"}, "skip_null_values"=false},
 *     denormalizationContext={"groups"={"filter:write"}},
 * )
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[\ApiPlatform\Metadata\ApiResource(
    denormalizationContext: ['groups' => []],
    normalizationContext: ['groups' => ['filter:read', 'time:read']],
)]
#[GetCollection(
    provider: AuthenticatedConstructionSiteProvider::class,
    security: "is_granted('ROLE_ASSOCIATED_CONSTRUCTION_MANAGER')",
    parameters: [
        'constructionSite' => new QueryParameter(filter: new IriFilter(),),
    ],
)]
class Filter extends BaseEntity
{
    use IdTrait;
    use AuthenticationTrait;
    use TimeTrait;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $isDeleted = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $isMarked = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $wasAddedWithClient = null;

    /**
     * @var string[]|null
     */
    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $numbers = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $state = null;

    /**
     * @var string[]|null
     */
    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $craftsmanIds = null;

    /**
     * @var string[]|null
     */
    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $mapIds = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deadlineBefore = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deadlineAfter = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $createdBy = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $registeredBy = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $closedBy = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $createdAtAfter = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $createdAtBefore = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $registeredAtAfter = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $registeredAtBefore = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $resolvedAtAfter = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $resolvedAtBefore = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $closedAtAfter = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $closedAtBefore = null;

    #[Groups(['filter:create'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $accessAllowedBefore = null;

    #[Groups(['filter:read', 'filter:create'])]
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: ConstructionSite::class, inversedBy: 'filters')]
    private ?ConstructionSite $constructionSite = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastUsedAt = null;

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

    public function getDeadlineBefore(): ?\DateTimeImmutable
    {
        return $this->deadlineBefore;
    }

    public function setDeadlineBefore(?\DateTimeImmutable $deadlineBefore): void
    {
        $this->deadlineBefore = $deadlineBefore;
    }

    public function getDeadlineAfter(): ?\DateTimeImmutable
    {
        return $this->deadlineAfter;
    }

    public function setDeadlineAfter(?\DateTimeImmutable $deadlineAfter): void
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

    public function getCreatedAtAfter(): ?\DateTimeImmutable
    {
        return $this->createdAtAfter;
    }

    public function setCreatedAtAfter(?\DateTimeImmutable $createdAtAfter): void
    {
        $this->createdAtAfter = $createdAtAfter;
    }

    public function getCreatedAtBefore(): ?\DateTimeImmutable
    {
        return $this->createdAtBefore;
    }

    public function setCreatedAtBefore(?\DateTimeImmutable $createdAtBefore): void
    {
        $this->createdAtBefore = $createdAtBefore;
    }

    public function getRegisteredAtAfter(): ?\DateTimeImmutable
    {
        return $this->registeredAtAfter;
    }

    public function setRegisteredAtAfter(?\DateTimeImmutable $registeredAtAfter): void
    {
        $this->registeredAtAfter = $registeredAtAfter;
    }

    public function getRegisteredAtBefore(): ?\DateTimeImmutable
    {
        return $this->registeredAtBefore;
    }

    public function setRegisteredAtBefore(?\DateTimeImmutable $registeredAtBefore): void
    {
        $this->registeredAtBefore = $registeredAtBefore;
    }

    public function getResolvedAtAfter(): ?\DateTimeImmutable
    {
        return $this->resolvedAtAfter;
    }

    public function setResolvedAtAfter(?\DateTimeImmutable $resolvedAtAfter): void
    {
        $this->resolvedAtAfter = $resolvedAtAfter;
    }

    public function getResolvedAtBefore(): ?\DateTimeImmutable
    {
        return $this->resolvedAtBefore;
    }

    public function setResolvedAtBefore(?\DateTimeImmutable $resolvedAtBefore): void
    {
        $this->resolvedAtBefore = $resolvedAtBefore;
    }

    public function getClosedAtAfter(): ?\DateTimeImmutable
    {
        return $this->closedAtAfter;
    }

    public function setClosedAtAfter(?\DateTimeImmutable $closedAtAfter): void
    {
        $this->closedAtAfter = $closedAtAfter;
    }

    public function getClosedAtBefore(): ?\DateTimeImmutable
    {
        return $this->closedAtBefore;
    }

    public function setClosedAtBefore(?\DateTimeImmutable $closedAtBefore): void
    {
        $this->closedAtBefore = $closedAtBefore;
    }

    public function getAccessAllowedBefore(): ?\DateTimeImmutable
    {
        return $this->accessAllowedBefore;
    }

    public function setAccessAllowedBefore(?\DateTimeImmutable $accessAllowedBefore): void
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

    public function getLastUsedAt(): ?\DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(): void
    {
        $this->lastUsedAt = new \DateTimeImmutable();
    }
}
