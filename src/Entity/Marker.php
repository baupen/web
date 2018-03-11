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
use App\Enum\EmailType;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Email is a sent email to the specified receivers.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\EmailRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Marker extends BaseEntity
{
    use IdTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $markXPercentage;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $markYPercentage;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $frameXPercentage;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $frameYPercentage;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $frameXHeight;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $frameYLength;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @var Craftsman
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Craftsman", inversedBy="makers")
     */
    private $craftsman;

    /**
     * @var BuildingMap
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\BuildingMap", inversedBy="markers")
     */
    private $buildingMap;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $imageFileName;

    /**
     * @return float
     */
    public function getMarkXPercentage()
    {
        return $this->markXPercentage;
    }

    /**
     * @param float $markXPercentage
     */
    public function setMarkXPercentage($markXPercentage)
    {
        $this->markXPercentage = $markXPercentage;
    }

    /**
     * @return float
     */
    public function getMarkYPercentage()
    {
        return $this->markYPercentage;
    }

    /**
     * @param float $markYPercentage
     */
    public function setMarkYPercentage($markYPercentage)
    {
        $this->markYPercentage = $markYPercentage;
    }

    /**
     * @return float
     */
    public function getFrameXPercentage()
    {
        return $this->frameXPercentage;
    }

    /**
     * @param float $frameXPercentage
     */
    public function setFrameXPercentage($frameXPercentage)
    {
        $this->frameXPercentage = $frameXPercentage;
    }

    /**
     * @return float
     */
    public function getFrameYPercentage()
    {
        return $this->frameYPercentage;
    }

    /**
     * @param float $frameYPercentage
     */
    public function setFrameYPercentage($frameYPercentage)
    {
        $this->frameYPercentage = $frameYPercentage;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return Craftsman
     */
    public function getCraftsman()
    {
        return $this->craftsman;
    }

    /**
     * @param Craftsman $craftsman
     */
    public function setCraftsman($craftsman)
    {
        $this->craftsman = $craftsman;
    }

    /**
     * @return BuildingMap
     */
    public function getBuildingMap()
    {
        return $this->buildingMap;
    }

    /**
     * @param BuildingMap $buildingMap
     */
    public function setBuildingMap($buildingMap)
    {
        $this->buildingMap = $buildingMap;
    }

    /**
     * @return string
     */
    public function getImageFileName()
    {
        return $this->imageFileName;
    }

    /**
     * @param string $imageFileName
     */
    public function setImageFileName($imageFileName)
    {
        $this->imageFileName = $imageFileName;
    }

    /**
     * @return float
     */
    public function getFrameXHeight()
    {
        return $this->frameXHeight;
    }

    /**
     * @param float $frameXHeight
     */
    public function setFrameXHeight($frameXHeight)
    {
        $this->frameXHeight = $frameXHeight;
    }

    /**
     * @return float
     */
    public function getFrameYLength()
    {
        return $this->frameYLength;
    }

    /**
     * @param float $frameYLength
     */
    public function setFrameYLength($frameYLength)
    {
        $this->frameYLength = $frameYLength;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
