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

    public function getStartX(): float
    {
        return $this->startX;
    }

    public function setStartX(float $startX): void
    {
        $this->startX = $startX;
    }

    public function getStartY(): float
    {
        return $this->startY;
    }

    public function setStartY(float $startY): void
    {
        $this->startY = $startY;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function setWidth(float $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function setHeight(float $height): void
    {
        $this->height = $height;
    }
}
