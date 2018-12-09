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

class Frame
{
    /**
     * @var float
     */
    private $startX;

    /**
     * @var float
     */
    private $startY;

    /**
     * @var float
     */
    private $width;

    /**
     * @var float
     */
    private $height;

    /**
     * @return float
     */
    public function getStartX(): float
    {
        return $this->startX;
    }

    /**
     * @param float $startX
     */
    public function setStartX(float $startX): void
    {
        $this->startX = $startX;
    }

    /**
     * @return float
     */
    public function getStartY(): float
    {
        return $this->startY;
    }

    /**
     * @param float $startY
     */
    public function setStartY(float $startY): void
    {
        $this->startY = $startY;
    }

    /**
     * @return float
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * @param float $width
     */
    public function setWidth(float $width): void
    {
        $this->width = $width;
    }

    /**
     * @return float
     */
    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * @param float $height
     */
    public function setHeight(float $height): void
    {
        $this->height = $height;
    }
}
