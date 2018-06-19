<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class IssuePosition
{
    /**
     * @var double
     *
     * @Assert\NotBlank()
     */
    private $x;

    /**
     * @var double
     *
     * @Assert\NotBlank()
     */
    private $y;

    /**
     * @var double
     *
     * @Assert\NotBlank()
     */
    private $zoomScale;

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
}