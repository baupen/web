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
use App\Entity\Traits\AutomaticEditTrait;
use App\Entity\Traits\IdTrait;
use App\Model\Point;
use Doctrine\ORM\Mapping as ORM;

/**
 * A map sector can be a specific room or area on the map.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class MapFileSector extends BaseEntity
{
    use IdTrait;
    use AutomaticEditTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @var MapFile
     *
     * @ORM\ManyToOne(targetEntity="MapFile", inversedBy="sectors")
     */
    private $mapFile;

    /**
     * @var Point[]|array
     *
     * @ORM\Column(type="json")
     */
    private $points;

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
     * @return MapFile
     */
    public function getMapFile(): MapFile
    {
        return $this->mapFile;
    }

    /**
     * @param MapFile $mapFile
     */
    public function setMapFile(MapFile $mapFile): void
    {
        $this->mapFile = $mapFile;
    }

    /**
     * @return Point[]|array
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param Point[]|array $points
     */
    public function setPoints($points): void
    {
        $this->points = $points;
    }
}
