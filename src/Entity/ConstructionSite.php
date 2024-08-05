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
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use App\Api\Filters\ExactSearchFilter;
use App\Api\Filters\IsDeletedFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * a construction site is the place the construction manager & the craftsmen work together.
 *
 * @ApiResource(
 *     collectionOperations={
 *      "get",
 *      "post" = {"security_post_denormalize" = "is_granted('CONSTRUCTION_SITE_CREATE', object)"}
 *      },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('CONSTRUCTION_SITE_VIEW', object)"},
 *      "patch" = {"security" = "is_granted('CONSTRUCTION_SITE_MODIFY', object)"}
 *     },
 *     normalizationContext={"groups"={"construction-site-read"}, "skip_null_values"=false},
 *     denormalizationContext={"groups"={"construction-site-write"}},
 *     attributes={"pagination_enabled"=false}
 * )
 *
 * @ApiFilter(ExactSearchFilter::class, properties={"constructionManagers.id": "exact"})
 * @ApiFilter(IsDeletedFilter::class, properties={"isDeleted"})
 * @ApiFilter(DateFilter::class, properties={"lastChangedAt"})
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks
 */
class ConstructionSite extends BaseEntity implements ConstructionSiteOwnedEntityInterface
{
    use IdTrait;
    use TimeTrait;
    use AddressTrait;
    use SoftDeleteTrait;

    /**
     * @Assert\NotBlank
     *
     * @Groups({"construction-site-read", "construction-site-write"})
     *
     * @ORM\Column(type="text")
     */
    private string $name;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $folderName = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionSiteImage", cascade={"persist"})
     */
    private ?ConstructionSiteImage $image = null;

    /**
     * @var Collection<int, \App\Entity\ConstructionManager>
     *
     * @Groups({"construction-site-read", "construction-site-write"})
     *
     * @ORM\ManyToMany(targetEntity="ConstructionManager", inversedBy="constructionSites")
     *
     * @ORM\JoinTable(name="construction_site_construction_manager")
     */
    private Collection $constructionManagers;

    /**
     * @var Collection<int, \App\Entity\Map>
     *
     * @ORM\OneToMany(targetEntity="Map", mappedBy="constructionSite", cascade={"persist"})
     *
     * @ORM\OrderBy({"name": "ASC"})
     */
    private Collection $maps;

    /**
     * @var Collection<int, \App\Entity\Craftsman>
     *
     * @ORM\OneToMany(targetEntity="Craftsman", mappedBy="constructionSite", cascade={"persist"})
     */
    private Collection $craftsmen;

    /**
     * @var Collection<int, \App\Entity\Issue>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Issue", mappedBy="constructionSite", cascade={"persist"})
     */
    private Collection $issues;

    /**
     * @var Collection<int, \App\Entity\EmailTemplate>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EmailTemplate", mappedBy="constructionSite", cascade={"persist"})
     *
     * @ORM\OrderBy({"purpose": "ASC", "name": "ASC"})
     */
    private Collection $emailTemplates;

    /**
     * @var Collection<int, \App\Entity\Filter>
     *
     * @ORM\OneToMany(targetEntity="Filter", mappedBy="constructionSite")
     */
    private Collection $filters;

    /**
     * @Groups({"construction-site-read"})
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $isHidden = false;

    /**
     * Construction site constructor.
     */
    public function __construct()
    {
        $this->constructionManagers = new ArrayCollection();
        $this->maps = new ArrayCollection();
        $this->craftsmen = new ArrayCollection();
        $this->issues = new ArrayCollection();
        $this->emailTemplates = new ArrayCollection();
        $this->filters = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getFolderName(): string
    {
        return $this->folderName;
    }

    /**
     * @return ConstructionManager[]|Collection
     */
    public function getConstructionManagers(): Collection|array
    {
        return $this->constructionManagers;
    }

    public function addConstructionManager(ConstructionManager $constructionManager): self
    {
        if (!$this->constructionManagers->contains($constructionManager)) {
            $this->constructionManagers[] = $constructionManager;
            $constructionManager->getConstructionSites()->add($this);
        }

        return $this;
    }

    public function removeConstructionManager(ConstructionManager $constructionManager): self
    {
        if ($this->constructionManagers->contains($constructionManager)) {
            $this->constructionManagers->removeElement($constructionManager);
            $constructionManager->getConstructionSites()->removeElement($this);
        }

        return $this;
    }

    /**
     * @return Map[]|Collection
     */
    public function getMaps(): Collection
    {
        return $this->maps;
    }

    /**
     * @return Craftsman[]|Collection
     */
    public function getCraftsmen(): Collection
    {
        return $this->craftsmen;
    }

    public function getImage(): ?ConstructionSiteImage
    {
        return $this->image;
    }

    public function setImage(?ConstructionSiteImage $image): void
    {
        $this->image = $image;
    }

    public function getIsHidden(): bool
    {
        return $this->isHidden;
    }

    public function setIsHidden(bool $isHidden): void
    {
        $this->isHidden = $isHidden;
    }

    /**
     * @return EmailTemplate[]|Collection
     */
    public function getEmailTemplates(): Collection
    {
        return $this->emailTemplates;
    }

    /**
     * @return Filter[]|Collection
     */
    public function getFilters(): Collection
    {
        return $this->filters;
    }

    /**
     * @return Issue[]|Collection
     */
    public function getIssues(): Collection
    {
        return $this->issues;
    }

    public function setFolderName(string $uniqueFolderName): void
    {
        $this->folderName = $uniqueFolderName;
    }

    /**
     * @Groups({"construction-site-read"})
     */
    public function getIsDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    /**
     * @Groups({"construction-site-read"})
     */
    public function getLastChangedAt(): \DateTime
    {
        return $this->lastChangedAt;
    }

    /**
     * @Groups({"construction-site-read"})
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function isConstructionSiteSet(): bool
    {
        return true;
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this;
    }
}
