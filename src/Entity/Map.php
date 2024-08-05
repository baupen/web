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

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filters\IsDeletedFilter;
use App\Api\Filters\RequiredExactSearchFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An Map is a logical plan of some part of the construction site.
 *
 * @ApiResource(
 *     collectionOperations={
 *      "get",
 *      "post" = {"security_post_denormalize" = "is_granted('MAP_MODIFY', object)", "denormalization_context"={"groups"={"map-create", "map-write"}}}
 *      },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('MAP_VIEW', object)"},
 *      "patch" = {"security" = "is_granted('MAP_MODIFY', object)"},
 *      "delete" = {"security" = "is_granted('MAP_MODIFY', object)"},
 *     },
 *     normalizationContext={"groups"={"map-read"}, "skip_null_values"=false},
 *     denormalizationContext={"groups"={"map-write"}},
 *     attributes={"pagination_enabled"=false}
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"id": "exact"})
 * @ApiFilter(RequiredExactSearchFilter::class, properties={"constructionSite"})
 * @ApiFilter(IsDeletedFilter::class, properties={"isDeleted"})
 * @ApiFilter(DateFilter::class, properties={"lastChangedAt"})
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Map extends BaseEntity implements ConstructionSiteOwnedEntityInterface
{
    use IdTrait;
    use TimeTrait;
    use SoftDeleteTrait;

    #[Assert\NotBlank]
    #[Groups(['map-read', 'map-write'])]
    #[ORM\Column(type: 'text')]
    private string $name;

    #[Assert\NotBlank]
    #[Groups(['map-create'])]
    #[ORM\ManyToOne(targetEntity: \ConstructionSite::class, inversedBy: 'maps')]
    private ?ConstructionSite $constructionSite = null;

    /**
     * @ApiProperty(readableLink=false, writableLink=false)
     */
    #[Groups(['map-read', 'map-write'])]
    #[ORM\ManyToOne(targetEntity: Map::class, inversedBy: 'children')]
    private ?self $parent = null;

    /**
     * @var Collection<int, Map>
     */
    #[ORM\OneToMany(targetEntity: Map::class, mappedBy: 'parent')]
    private Collection $children;

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

    public function isConstructionSiteSet(): bool
    {
        return null !== $this->constructionSite;
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

            return $parentContext.$this->getParent()->getName();
        }

        return '';
    }

    public function getNameWithContext(): string
    {
        $context = $this->getContext();
        if (mb_strlen($context) > 0) {
            $context .= ' > ';
        }

        return $context.$this->getName();
    }

    public function getFile(): ?MapFile
    {
        return $this->file;
    }

    public function setFile(?MapFile $file): void
    {
        $this->file = $file;
    }

    #[Groups(['map-read'])]
    public function getIsDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    #[Groups(['map-read'])]
    public function getLastChangedAt(): \DateTimeInterface
    {
        return $this->lastChangedAt;
    }
}
