<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class IssuePosition
{
    /**
     * @var float
     *
     * @Assert\NotBlank()
     */
    private $x;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     */
    private $y;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     */
    private $zoomScale;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $mapFileId;

    /**
     * @return float
     */
    public function getX(): float
    {
        return $this->x;
    }

    /**
     * @param float $x
     */
    public function setX(float $x): void
    {
        $this->x = $x;
    }

    /**
     * @return float
     */
    public function getY(): float
    {
        return $this->y;
    }

    /**
     * @param float $y
     */
    public function setY(float $y): void
    {
        $this->y = $y;
    }

    /**
     * @return float
     */
    public function getZoomScale(): float
    {
        return $this->zoomScale;
    }

    /**
     * @param float $zoomScale
     */
    public function setZoomScale(float $zoomScale): void
    {
        $this->zoomScale = $zoomScale;
    }

    /**
     * @return string
     */
    public function getMapFileId(): string
    {
        return $this->mapFileId;
    }

    /**
     * @param string $mapFileId
     */
    public function setMapFileId(string $mapFileId): void
    {
        $this->mapFileId = $mapFileId;
    }
}
