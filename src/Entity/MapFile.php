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
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class MapFile extends BaseEntity
{
    use IdTrait;
    use TimeTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $hash;

    /**
     * @var Map
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Map", inversedBy="children")
     */
    private $map;

    /**
     * @var Issue[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Issue", mappedBy="map")
     */
    private $issues;

    public function __construct()
    {
        $this->issues = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return Map
     */
    public function getMap(): Map
    {
        return $this->map;
    }

    /**
     * @param Map $map
     */
    public function setMap(Map $map): void
    {
        $this->map = $map;
    }

    /**
     * @return Issue[]|ArrayCollection
     */
    public function getIssues()
    {
        return $this->issues;
    }
}
