<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\IriFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Api\Filters\IsDeletedFilter;
use App\Api\Filters\RequiredExactSearchFilter;
use App\Api\Provider\AuthenticatedCollectionProvider;
use App\Api\Provider\CraftsmanStatisticsProvider;
use App\Api\Provider\IssueCollectionProvider;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Traits\AuthenticationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * a craftsman receives information about open issues, and answers them.
 *
 * @ApiResource(
 *     collectionOperations={
 *      "get",
 *      "post" = {"security_post_denormalize" = "is_granted('CRAFTSMAN_MODIFY', object)", "denormalization_context"={"groups"={"craftsman:create", "craftsman:write"}}},
 *      "get_statistics"={
 *          "method"="GET",
 *          "path"="/craftsmen/statistics"
 *      }
 *      },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('CRAFTSMAN_VIEW', object)"},
 *      "patch" = {"security" = "is_granted('CRAFTSMAN_MODIFY', object)"},
 *      "delete" = {"security" = "is_granted('CRAFTSMAN_MODIFY', object)"},
 *     },
 *     normalizationContext={"groups"={"craftsman:read"}, "skip_null_values"=false},
 *     denormalizationContext={"groups"={"craftsman:write"}},
 *     attributes={"pagination_enabled"=false}
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "trade": "exact"})
 * @ApiFilter(RequiredExactSearchFilter::class, properties={"constructionSite"})
 * @ApiFilter(IsDeletedFilter::class, properties={"isDeleted"})
 * @ApiFilter(DateFilter::class, properties={"lastChangedAt"})
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[\ApiPlatform\Metadata\ApiResource(
    denormalizationContext: ['groups' => ['craftsman:write']],
    normalizationContext: ['groups' => ['craftsman:read', 'time:read', 'soft-delete:read']],
)]
#[GetCollection(
    provider: AuthenticatedCollectionProvider::class,
    parameters: [
        'constructionSite' => new QueryParameter(filter: new IriFilter(),),
    ],
)]
#[GetCollection(uriTemplate: '/craftsmen/statistics', provider: CraftsmanStatisticsProvider::class, normalizationContext: ['groups' => ['craftsman-statistics:read']], paginationEnabled: false)]
class Craftsman extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use AuthenticationTrait;
    use SoftDeleteTrait;

    #[Assert\NotBlank]
    #[Groups(['craftsman:read', 'craftsman:write'])]
    #[ORM\Column(type: Types::TEXT)]
    private string $contactName;

    #[Groups(['craftsman:read', 'craftsman:write'])]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $contactJobTitle;

    #[Assert\NotBlank]
    #[Groups(['craftsman:read', 'craftsman:write'])]
    #[ORM\Column(type: Types::TEXT)]
    private string $company;

    #[Assert\NotBlank]
    #[Groups(['craftsman:read', 'craftsman:write'])]
    #[ORM\Column(type: Types::TEXT)]
    private string $trade;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['craftsman:read', 'craftsman:write'])]
    #[ORM\Column(type: Types::TEXT)]
    private string $email;

    #[Groups(['craftsman:read', 'craftsman:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $telephone;

    #[Groups(['craftsman:read', 'craftsman:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address;

    /**
     * @var string[]
     */
    #[Groups(['craftsman:read', 'craftsman:write'])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $emailCCs = null;

    #[Groups(['craftsman:read', 'craftsman:write'])]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $canEdit = true;

    #[Assert\NotBlank]
    #[Groups(['craftsman:create'])]
    #[ORM\ManyToOne(targetEntity: ConstructionSite::class, inversedBy: 'craftsmen')]
    private ?ConstructionSite $constructionSite = null;

    /**
     * @var Collection<int, Issue>
     */
    #[ORM\OneToMany(targetEntity: Issue::class, mappedBy: 'craftsman')]
    private Collection $issues;

    /**
     * @var Collection<int, Issue>
     */
    #[ORM\OneToMany(targetEntity: Issue::class, mappedBy: 'resolvedBy')]
    private Collection $resolvedIssues;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastEmailReceived = null;

    #[Groups(['craftsman:read'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastVisitOnline = null;

    /**
     * Craftsman constructor.
     */
    public function __construct()
    {
        $this->issues = new ArrayCollection();
        $this->resolvedIssues = new ArrayCollection();
    }

    public function getContactName(): string
    {
        return $this->contactName;
    }

    public function setContactName(string $contactName): void
    {
        $this->contactName = $contactName;
    }

    public function getContactJobTitle(): ?string
    {
        return $this->contactJobTitle;
    }

    public function setContactJobTitle(?string $contactJobTitle): void
    {
        $this->contactJobTitle = $contactJobTitle;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    public function getTrade(): string
    {
        return $this->trade;
    }

    public function setTrade(string $trade): void
    {
        $this->trade = $trade;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): void
    {
        $this->telephone = $telephone;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return string[]
     */
    public function getEmailCCs(): array
    {
        return $this->emailCCs;
    }

    /**
     * @param string[] $emailCCs
     */
    public function setEmailCCs(array $emailCCs): void
    {
        $this->emailCCs = $emailCCs;
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    /**
     * @return Collection<int, Issue>
     */
    public function getIssues(): Collection
    {
        return $this->issues;
    }

    /**
     * @return Collection<int, Issue>
     */
    public function getResolvedIssues(): Collection
    {
        return $this->resolvedIssues;
    }

    public function getName(): string
    {
        return $this->getTrade() . ' (' . $this->getCompany() . ')';
    }

    public function getLastEmailReceived(): ?\DateTimeImmutable
    {
        return $this->lastEmailReceived;
    }

    public function setLastEmailReceived(?\DateTimeImmutable $lastEmailReceived): void
    {
        $this->lastEmailReceived = $lastEmailReceived;
    }

    public function getLastVisitOnline(): ?\DateTimeImmutable
    {
        return $this->lastVisitOnline;
    }

    public function setLastVisitOnline(?\DateTimeImmutable $lastVisitOnline): void
    {
        $this->lastVisitOnline = $lastVisitOnline;
    }

    public function getLastAction(): ?\DateTimeImmutable
    {
        $lastAction = $this->getLastVisitOnline();
        if (!$lastAction instanceof \DateTimeImmutable || $lastAction < $this->getLastEmailReceived()) {
            return $this->getLastEmailReceived();
        }

        return $lastAction;
    }

    public function sort(Craftsman $other): int
    {
        return strcmp($this->getCompany(), $other->getCompany());
    }

    public function getCanEdit(): bool
    {
        return $this->canEdit;
    }

    public function setCanEdit(bool $canEdit): void
    {
        $this->canEdit = $canEdit;
    }
}
