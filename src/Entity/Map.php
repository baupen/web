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

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Map is a logical plan of some part of the construction site.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MapRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Map extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use SoftDeleteTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @var ConstructionSite
     *
     * @ORM\ManyToOne(targetEntity="ConstructionSite", inversedBy="maps")
     */
    private $constructionSite;

    /**
     * @var Map|null
     *
     * @ApiProperty(readableLink=false, writableLink=false)
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
     * @var MapFile[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\MapFile", mappedBy="map", cascade={"persist"})
     */
    private $files;

    /**
     * @var MapFile
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MapFile")
     */
    private $file;

    /**
     * @var Issue[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Issue", mappedBy="map")
     */
    private $issues;

    public function __construct()
    {
        $this->files = new ArrayCollection();
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

    /**
     * @return Map|null
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @param Map|null $parent
     */
    public function setParent(?self $parent): void
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

    /**
     * @return string
     */
    public function getContext()
    {
        if (null !== $this->getParent()) {
            $parentContext = $this->getParent()->getContext();
            if ('' !== $parentContext) {
                $parentContext .= ' > ';
            }

            return $parentContext.$this->getParent()->getName();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getNameWithContext()
    {
        $context = $this->getContext();
        if (mb_strlen($context) > 0) {
            $context .= ' > ';
        }

        return $context.$this->getName();
    }

    /**
     * @return MapFile[]|ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
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
