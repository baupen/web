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
 * a craftsman receives information about open issues, and answers them.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Craftsman extends BaseEntity
{
    use IdTrait;

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
    private $trade;

    /**
     * @var Issue[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Issue", mappedBy="craftsman")
     */
    private $markers;

    /**
     * @var ConstructionSite
     *
     * @ORM\ManyToOne(targetEntity="ConstructionSite", mappedBy="craftsmen")
     */
    private $building;

    /**
     * Craftsman constructor.
     */
    public function __construct()
    {
        $this->markers = new ArrayCollection();
    }

    /**
     * @return Issue[]|ArrayCollection
     */
    public function getMarkers()
    {
        return $this->markers;
    }

    /**
     * remove all array collections, setting them to null
     */
    public function flattenDoctrineStructures()
    {
        $this->markers = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
