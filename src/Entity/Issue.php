<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Api\CustomController\IssuesRender;
use App\Api\CustomController\IssuesReport;
use App\Api\CustomController\IssuesSummary;
use App\Api\Filters\IsDeletedFilter;
use App\Api\Filters\StateFilter;
use App\Api\Processor\IssueReportProcessor;
use App\Api\Provider\CraftsmanStatisticsProvider;
use App\Api\Provider\IssueProvider;
use App\Api\Provider\IssueSummaryProvider;
use App\Api\Provider\IssueTimeseriesProvider;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Issue\IssuePositionTrait;
use App\Entity\Issue\IssueStatusTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Repository\IssueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * An issue is something created by the construction manager to inform the craftsman of it.
 *
 * @ApiResource(
 *     collectionOperations={
 *      "get",
 *      "post" = {"security_post_denormalize" = "is_granted('ISSUE_MODIFY', object)", "denormalization_context"={"groups"={"issue:create", "issue:write"}}},
 *      "get_group"={
 *          "method"="GET",
 *          "path"="/issues/group"
 *      },
 *      "get_render"={
 *          "method"="GET",
 *          "path"="/issues/render.jpg",
 *          "controller"=IssuesRender::class,
 *          "formats"={"jpeg"},
 *      },
 *      "get_report"={
 *          "method"="GET",
 *          "path"="/issues/report",
 *          "controller"=IssuesReport::class,
 *          "formats"={"pdf"},
 *      },
 *      "get_summary"={
 *          "method"="GET",
 *          "path"="/issues/summary",
 *          "controller"=IssuesSummary::class
 *      },
 *      "get_timeseries"={
 *          "method"="GET",
 *          "path"="/issues/timeseries"
 *      }
 *      },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('ISSUE_VIEW', object)"},
 *      "patch" = {"security" = "is_granted('ISSUE_MODIFY', object) or is_granted('ISSUE_RESPOND', object)", "security_post_denormalize" = "is_granted('ISSUE_MODIFY', object) or is_granted('ISSUE_RESPOND', object)"},
 *      "delete" = {"security" = "is_granted('ISSUE_MODIFY', object)"},
 *     },
 *     denormalizationContext={"groups"={}}, // set depending on the context
 *     normalizationContext={"groups"={"issue:read"}, "skip_null_values"=false}
 * )
 *
 * @ApiFilter(RequiredExactSearchFilter::class, properties={"constructionSite"})
 * @ApiFilter(IsDeletedFilter::class, properties={"isDeleted"})
 * @ApiFilter(DateFilter::class, properties={"lastChangedAt", "createdAt", "registeredAt", "resolvedAt", "closedAt", "deadline"})
 * @ApiFilter(BooleanFilter::class, properties={"isMarked", "wasAddedWithClient"})
 * @ApiFilter(NumericFilter::class, properties={"number"})
 * @ApiFilter(SearchFilter::class, properties={"craftsman": "exact", "map": "exact", "createdBy": "exact", "registeredBy": "exact", "closedBy": "exact", "description": "partial"})
 * @ApiFilter(StateFilter::class, properties={"state"})
 * @ApiFilter(PatchedOrderFilter::class, properties={"lastChangedAt": "ASC", "deadline"={"nulls_comparison": PatchedOrderFilter::NULLS_ALWAYS_LAST, "default_direction": "ASC"}, "number": "ASC", "craftsman.trade": "ASC", "map.name": "ASC", "description": {"nulls_comparison": PatchedOrderFilter::NULLS_ALWAYS_LAST, "default_direction": "ASC"}})
 */
#[ORM\Entity(repositoryClass: IssueRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    provider: IssueProvider::class,
    denormalizationContext: ['groups' => ['issue:write']],
    normalizationContext: ['groups' => ['issue:read', 'soft-delete:read']],
)]

#[Get(uriTemplate: '/issues/summary', provider: IssueSummaryProvider::class, normalizationContext: ['groups' => ['issue-summary:read']], paginationEnabled: false)]
#[Get(uriTemplate: '/issues/timeseries', provider: IssueTimeseriesProvider::class, normalizationContext: ['groups' => ['issue-summary:read']], paginationEnabled: false)]
#[Get(uriTemplate: '/issues/report', paginationEnabled: false, controller: IssuesReport::class)]
#[Get(uriTemplate: '/issues/render', paginationEnabled: false, controller: IssuesRender::class)]
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
    #[ORM\ManyToOne(targetEntity: Craftsman::class, inversedBy: 'issues')]
    private ?Craftsman $craftsman = null;

    #[Assert\NotBlank]
    #[Groups(['issue:read', 'issue:write'])]
    #[ORM\ManyToOne(targetEntity: Map::class, inversedBy: 'issues')]
    private ?Map $map = null;

    #[Assert\NotBlank]
    #[Groups(['issue:create'])]
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

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }
}
