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
use Doctrine\ORM\Mapping as ORM;

/**
 * An issue is something created by the construction manager to inform the craftsman of it.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class IssuePosition extends BaseEntity
{
    use IdTrait;
    use TimeTrait;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $positionX;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $positionY;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $positionZoomScale;

    /**
     * @var Issue
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Issue", inversedBy="position")
     */
    private $issue;

    /**
     * @var MapFile
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MapFile", inversedBy="issuePositions")
     */
    private $mapFile;

    /**
     * @return float|null
     */
    public function getPositionX(): float
    {
        return $this->positionX;
    }

    /**
     * @param float|null $positionX
     */
    public function setPositionX(float $positionX): void
    {
        $this->positionX = $positionX;
    }

    /**
     * @return float|null
     */
    public function getPositionY(): float
    {
        return $this->positionY;
    }

    /**
     * @param float|null $positionY
     */
    public function setPositionY(float $positionY): void
    {
        $this->positionY = $positionY;
    }

    /**
     * @return float|null
     */
    public function getPositionZoomScale(): float
    {
        return $this->positionZoomScale;
    }

    /**
     * @param float|null $positionZoomScale
     */
    public function setPositionZoomScale(float $positionZoomScale): void
    {
        $this->positionZoomScale = $positionZoomScale;
    }

    public function getIssue(): Issue
    {
        return $this->issue;
    }

    public function setIssue(Issue $issue): void
    {
        $this->issue = $issue;
    }

    public function getMapFile(): MapFile
    {
        return $this->mapFile;
    }

    public function setMapFile(MapFile $mapFile): void
    {
        $this->mapFile = $mapFile;
    }
}
