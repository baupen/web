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

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * a construction site is the place the construction manager & the craftsmen work together.
 *
 * @ORM\Table(name="construction_site")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ConstructionSite extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use AddressTrait;
    use SoftDeleteTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $folderName;

    /**
     * @var ConstructionSiteImage[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ConstructionSiteImage", mappedBy="constructionSite", cascade={"persist"})
     */
    private $images;

    /**
     * @var ConstructionSiteImage|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionSiteImage")
     */
    private $image;

    /**
     * @var ConstructionManager[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ConstructionManager", inversedBy="constructionSites")
     * @ORM\JoinTable(name="construction_site_construction_manager")
     */
    private $constructionManagers;

    /**
     * @var Map[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Map", mappedBy="constructionSite", cascade={"persist"})
     * @ORM\OrderBy({"name": "ASC"})
     */
    private $maps;

    /**
     * @var Craftsman[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Craftsman", mappedBy="constructionSite", cascade={"persist"})
     */
    private $craftsmen;

    /**
     * @var Issue[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Issue", mappedBy="constructionSite", cascade={"persist"})
     */
    private $issues;

    /**
     * @var Filter[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Filter", mappedBy="constructionSite")
     */
    private $filters;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isTrialConstructionSite = false;

    /**
     * Construction site constructor.
     */
    public function __construct()
    {
        $this->constructionManagers = new ArrayCollection();
        $this->maps = new ArrayCollection();
        $this->craftsmen = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->issues = new ArrayCollection();
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
     * @return ConstructionManager[]|ArrayCollection
     */
    public function getConstructionManagers()
    {
        return $this->constructionManagers;
    }

    /**
     * @return Map[]|ArrayCollection
     */
    public function getMaps()
    {
        return $this->maps;
    }

    /**
     * @return string[]
     */
    public function getMapIds()
    {
        $ids = [];
        foreach ($this->getMaps() as $map) {
            $ids[] = $map->getId();
        }

        return $ids;
    }

    /**
     * @return Craftsman[]|ArrayCollection
     */
    public function getCraftsmen()
    {
        return $this->craftsmen;
    }

    /**
     * @return ConstructionSiteImage[]|ArrayCollection
     */
    public function getImages()
    {
        return $this->images;
    }

    public function getImage(): ?ConstructionSiteImage
    {
        return $this->image;
    }

    public function setImage(?ConstructionSiteImage $image): void
    {
        $this->image = $image;
    }

    public function isTrialConstructionSite(): bool
    {
        return $this->isTrialConstructionSite;
    }

    public function setIsTrialConstructionSite(bool $isTrialConstructionSite): void
    {
        $this->isTrialConstructionSite = $isTrialConstructionSite;
    }

    /**
     * @return Filter[]|ArrayCollection
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return Issue[]|ArrayCollection
     */
    public function getIssues()
    {
        return $this->issues;
    }

    public function setFolderName(string $uniqueFolderName): void
    {
        $this->folderName = $uniqueFolderName;
    }
}
