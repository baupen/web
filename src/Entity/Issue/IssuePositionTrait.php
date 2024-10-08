<?php

/*
 * This file is part of the baupen project.
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
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait IssuePositionTrait
{
    #[Assert\NotBlank(groups: ['position'])]
    #[Groups(['issue-read', 'issue-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::FLOAT, nullable: true)]
    private ?float $positionX = null;

    #[Assert\NotBlank(groups: ['position'])]
    #[Groups(['issue-read', 'issue-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::FLOAT, nullable: true)]
    private ?float $positionY = null;

    #[Assert\NotBlank(groups: ['position'])]
    #[Groups(['issue-read', 'issue-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::FLOAT, nullable: true)]
    private ?float $positionZoomScale = null;

    #[Assert\Callback]
    public function validatePosition(ExecutionContextInterface $context): void
    {
        $nullCount = 0;
        $nullCount += (null === $this->getPositionX()) ? 1 : 0;
        $nullCount += (null === $this->getPositionY()) ? 1 : 0;
        $nullCount += (null === $this->getPositionZoomScale()) ? 1 : 0;

        if ($nullCount > 0 && $nullCount < 3) {
            $context->buildViolation('Position properties must be all null or all not null!')->addViolation();
        }
    }

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

    public function hasPosition(): bool
    {
        return null !== $this->getPositionX();
    }
}
