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
class MapSector extends BaseEntity
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
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $color;

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
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $identifier;

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
        if (\count($this->points) === 0 || \is_object($this->points[0])) {
            return $this->points;
        }

        // doctrine deserializes to associative array instead of object
        $res = [];
        foreach ($this->points as $point) {
            $res[] = (object) $point;
        }

        return $res;
    }

    /**
     * @param Point[]|array $points
     */
    public function setPoints($points): void
    {
        $this->points = $points;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    /**
     * @param MapSector|null $other
     *
     * @return bool
     */
    public function equals(?self $other)
    {
        if ($other === null || $this->getName() !== $other->getName() || $this->getColor() !== $other->getColor()) {
            return false;
        }

        return json_encode($this->getPoints()) === json_encode($other->getPoints());
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }
}
