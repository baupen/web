<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\IriFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Api\Filters\IsDeletedFilter;
use App\Api\Provider\AuthenticatedCollectionProvider;
use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    denormalizationContext: ['groups' => ['map:write']],
    normalizationContext: ['groups' => ['map:read', 'time:read', 'soft-delete:read'], "skip_null_values" => false],
)]
#[GetCollection(
    provider: AuthenticatedCollectionProvider::class,
    paginationEnabled: false,
    parameters: [
        'id' => new QueryParameter(filter: new IriFilter(),),
        'constructionSite' => new QueryParameter(filter: new IriFilter(),),
    ],
)]
#[Get(security: 'is_granted("MAP_VIEW", object)')]
#[Post(securityPostDenormalize: 'is_granted("MAP_MODIFY", object)', denormalizationContext: ['groups' => ['map:create', 'map:write']])]
#[Patch(security: 'is_granted("MAP_MODIFY", object)')]
#[Delete(security: 'is_granted("MAP_MODIFY", object)')]
#[ApiFilter(DateFilter::class, properties: ['lastChangedAt'])]
#[ApiFilter(IsDeletedFilter::class)]
class Map extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use SoftDeleteTrait;

    #[Assert\NotBlank]
    #[Groups(['map:read', 'map:write'])]
    #[ORM\Column(type: Types::TEXT)]
    private string $name;

    #[Assert\NotBlank]
    #[Groups(['map:create'])]
    #[ORM\ManyToOne(targetEntity: ConstructionSite::class, inversedBy: 'maps')]
    private ?ConstructionSite $constructionSite = null;

    /**
     * @ApiProperty(readableLink=false, writableLink=false)
     */
    #[Groups(['map:read', 'map:write'])]
    #[ORM\ManyToOne(targetEntity: Map::class, inversedBy: 'children')]
    private ?self $parent = null;

    /**
     * @var Collection<int, Map>
     */
    #[ORM\OneToMany(targetEntity: Map::class, mappedBy: 'parent')]
    private Collection $children;

    #[Groups(['map:read'])]
    #[ORM\ManyToOne(targetEntity: MapFile::class, cascade: ['persist'])]
    private ?MapFile $file = null;

    /**
     * @var Collection<int, Issue>
     */
    #[ORM\OneToMany(targetEntity: Issue::class, mappedBy: 'map')]
    private Collection $issues;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->issues = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Collection<int, Map>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @return Collection<int, Issue>
     */
    public function getIssues(): Collection
    {
        return $this->issues;
    }

    public function getContext(): string
    {
        if ($this->getParent() instanceof Map) {
            $parentContext = $this->getParent()->getContext();
            if ('' !== $parentContext) {
                $parentContext .= ' > ';
            }

            return $parentContext . $this->getParent()->getName();
        }

        return '';
    }

    public function getNameWithContext(): string
    {
        $context = $this->getContext();
        if (mb_strlen($context) > 0) {
            $context .= ' > ';
        }

        return $context . $this->getName();
    }

    public function getFile(): ?MapFile
    {
        return $this->file;
    }

    public function setFile(?MapFile $file): void
    {
        $this->file = $file;
    }
}
