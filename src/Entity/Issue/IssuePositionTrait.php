<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Issue;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait IssuePositionTrait
{
    /**
     * @var float|null
     *
     * @Assert\NotBlank(groups={"position"})
     * @Groups({"issue-read", "issue-write"})
     * @ORM\Column(type="float", nullable=true)
     */
    private $positionX;

    /**
     * @var float|null
     *
     * @Assert\NotBlank(groups={"position"})
     * @Groups({"issue-read", "issue-write"})
     * @ORM\Column(type="float", nullable=true)
     */
    private $positionY;

    /**
     * @var float|null
     *
     * @Assert\NotBlank(groups={"position"})
     * @Groups({"issue-read", "issue-write"})
     * @ORM\Column(type="float", nullable=true)
     */
    private $positionZoomScale;

    public function getPositionX(): ?float
    {
        return $this->positionX;
    }

    public function setPositionX(?float $positionX): void
    {
        $this->positionX = $positionX;
    }

    public function getPositionY(): ?float
    {
        return $this->positionY;
    }

    public function setPositionY(?float $positionY): void
    {
        $this->positionY = $positionY;
    }

    public function getPositionZoomScale(): ?float
    {
        return $this->positionZoomScale;
    }

    public function setPositionZoomScale(?float $positionZoomScale): void
    {
        $this->positionZoomScale = $positionZoomScale;
    }
}
