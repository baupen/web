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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Email is a sent email to the specified receivers.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Map extends BaseEntity
{
    use IdTrait;

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
}
