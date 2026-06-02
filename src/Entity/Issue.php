<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\OrderFilterInterface;
use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filters\IsDeletedFilter;
use App\Api\Filters\StateFilter;
use App\Api\Processor\IssueProcessor;
use App\Api\Provider\IssueCollectionProvider;
use App\Api\Provider\IssueGroupProvider;
use App\Api\Provider\IssueSummaryProvider;
use App\Api\Provider\IssueTimeseriesProvider;
use App\Entity\Base\BaseEntity;
use App\Entity\Issue\IssuePositionTrait;
use App\Entity\Issue\IssueStatusTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Repository\IssueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: IssueRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    processor: IssueProcessor::class,
    denormalizationContext: ['groups' => []],
    normalizationContext: ['groups' => ['issue:read', 'soft-delete:read'], "skip_null_values" => false]
)]
#[GetCollection(provider: IssueCollectionProvider::class)]
#[GetCollection(uriTemplate: '/issues/summary', provider: IssueSummaryProvider::class, normalizationContext: ['groups' => ['issue-summary:read'], "skip_null_values" => false], paginationEnabled: false)]
#[GetCollection(uriTemplate: '/issues/timeseries', provider: IssueTimeseriesProvider::class, normalizationContext: ['groups' => ['issue-summary:read'], "skip_null_values" => false], paginationEnabled: false)]
#[GetCollection(uriTemplate: '/issues/group', provider: IssueGroupProvider::class, normalizationContext: ['groups' => ['issue-group:read'], "skip_null_values" => false], paginationEnabled: false)]
#[GetCollection(uriTemplate: '/issues/report', provider: IssueCollectionProvider::class, paginationEnabled: false)]
#[GetCollection(uriTemplate: '/issues/render.jpg', provider: IssueCollectionProvider::class, paginationEnabled: false, formats: ['jpeg'])]
#[Get(security: 'is_granted("ISSUE_VIEW", object)')]
#[Post(securityPostDenormalize: 'is_granted("ISSUE_MODIFY", object)', denormalizationContext: ['groups' => ['issue:create', 'issue:write']])]
#[Patch(security: 'is_granted("ISSUE_MODIFY", object) or is_granted("ISSUE_RESPOND", object)')]
#[Delete(security: 'is_granted("ISSUE_MODIFY", object)')]
#[ApiFilter(SearchFilter::class, properties: ['constructionSite', 'craftsman', 'map', 'createdBy', 'registeredBy', 'closedBy', 'description' => SearchFilterInterface::STRATEGY_IPARTIAL], strategy: SearchFilter::STRATEGY_EXACT)]
#[ApiFilter(IsDeletedFilter::class)]
#[ApiFilter(DateFilter::class, properties: ['lastChangedAt', "createdAt", "registeredAt", "resolvedAt", "closedAt", "deadline"])]
#[ApiFilter(BooleanFilter::class, properties: ['isMarked', 'wasAddedWithClient'])]
#[ApiFilter(NumericFilter::class, properties: ['number'])]
#[ApiFilter(OrderFilter::class, properties: ['lastChangedAt', 'number', 'craftsman.trade', 'map.name', 'description', 'deadline'], strategy: OrderFilterInterface::NULLS_ALWAYS_LAST)]
#[ApiFilter(StateFilter::class)]
class Issue extends BaseEntity
{
    use IdTrait;
    use SoftDeleteTrait;
    use IssuePositionTrait;
    use IssueStatusTrait;

    #[Groups(['issue:read'])]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $number = null;

    #[Groups(['issue:read', 'issue:write'])]
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isMarked = false;

    #[Groups(['issue:read', 'issue:write'])]
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $wasAddedWithClient = false;

    #[Assert\NotBlank(groups: ['after-register'])]
    #[Groups(['issue:read', 'issue:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['issue:read', 'issue:write'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deadline = null;

    #[Groups(['issue:read'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $lastChangedAt = null;

    #[Assert\Callback]
    public function validateRelations(ExecutionContextInterface $context): void
    {
        if (null !== $this->craftsman && $this->craftsman->getConstructionSite() !== $this->constructionSite) {
            $context->buildViolation('Craftsman does not belong to construction site')->addViolation();
        }

        if (null !== $this->map && $this->map->getConstructionSite() !== $this->constructionSite) {
            $context->buildViolation('Map does not belong to construction site')->addViolation();
        }
    }

    #[Groups(['issue:read'])]
    #[ORM\ManyToOne(targetEntity: IssueImage::class, cascade: ['persist'])]
    private ?IssueImage $image = null;

    #[Assert\NotBlank(groups: ['after-register'])]
    #[Groups(['issue:read', 'issue:write'])]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\ManyToOne(targetEntity: Craftsman::class, inversedBy: 'issues')]
    private ?Craftsman $craftsman = null;

    #[Assert\NotBlank]
    #[Groups(['issue:read', 'issue:write'])]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\ManyToOne(targetEntity: Map::class, inversedBy: 'issues')]
    private ?Map $map = null;

    #[Assert\NotBlank]
    #[Groups(['issue:create'])]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\ManyToOne(targetEntity: ConstructionSite::class, inversedBy: 'issues')]
    private ?ConstructionSite $constructionSite = null;

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function prePersistTime(): void
    {
        $this->lastChangedAt = new \DateTimeImmutable();
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

    public function getDeadline(): ?\DateTimeImmutable
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTimeImmutable $deadline): void
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

    public function getConstructionSite(): ?ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    public function getLastChangedAt(): ?\DateTimeImmutable
    {
        return $this->lastChangedAt;
    }
}
