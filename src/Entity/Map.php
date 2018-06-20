<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;


use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Email is a sent email to the specified receivers.
 *
 * @ORM\Table(name="map")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Map extends BaseEntity
{
    use IdTrait;
    use TimeTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $filename;

    /**
     * @var ConstructionSite|null
     *
     * @ORM\ManyToOne(targetEntity="ConstructionSite", inversedBy="maps")
     */
    private $constructionSite;

    /**
     * @var Map|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Map", inversedBy="children")
     */
    private $parent;

    /**
     * @var Map[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Map", mappedBy="parent")
     */
    private $children;

    /**
     * @var Issue[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Issue", mappedBy="map")
     */
    private $issues;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->issues = new ArrayCollection();
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
     * @return null|string
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param null|string $filename
     */
    public function setFilename(?string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return ConstructionSite|null
     */
    public function getConstructionSite(): ?ConstructionSite
    {
        return $this->constructionSite;
    }

    /**
     * @param ConstructionSite|null $constructionSite
     */
    public function setConstructionSite(?ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    /**
     * @return Map|null
     */
    public function getParent(): ?Map
    {
        return $this->parent;
    }

    /**
     * @param Map|null $parent
     */
    public function setParent(?Map $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Map[]|ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return Issue[]|ArrayCollection
     */
    public function getIssues()
    {
        return $this->issues;
    }
}
