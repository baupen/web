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
use App\Entity\Traits\AutomaticEditTrait;
use App\Entity\Traits\IdTrait;
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
    use AutomaticEditTrait;

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
     * @ORM\OneToMany(targetEntity="App\Entity\ConstructionSiteImage", mappedBy="constructionSite")
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
     * @ORM\OneToMany(targetEntity="Map", mappedBy="constructionSite")
     * @ORM\OrderBy({"name": "ASC"})
     */
    private $maps;

    /**
     * @var Craftsman[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Craftsman", mappedBy="constructionSite")
     */
    private $craftsmen;

    /**
     * Construction site constructor.
     */
    public function __construct()
    {
        $this->constructionManagers = new ArrayCollection();
        $this->maps = new ArrayCollection();
        $this->craftsmen = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getFolderName(): string
    {
        return $this->folderName;
    }

    /**
     * @param string $folderName
     */
    public function setFolderName(string $folderName): void
    {
        $this->folderName = $folderName;
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

    /**
     * @return ConstructionSiteImage|null
     */
    public function getImage(): ?ConstructionSiteImage
    {
        return $this->image;
    }

    /**
     * @param ConstructionSiteImage|null $image
     */
    public function setImage(?ConstructionSiteImage $image): void
    {
        $this->image = $image;
    }
}
