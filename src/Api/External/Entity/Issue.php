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

use App\Api\External\Entity\Base\BaseEntity;
use Symfony\Component\Validator\Constraints as Assert;

class Issue extends BaseEntity
{
    /**
     * @var int|null
     */
    private $number;

    /**
     * @var bool
     */
    private $isMarked;

    /**
     * @var bool
     */
    private $wasAddedWithClient;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $craftsman;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $map;

    /**
     * @var File|null
     */
    private $image;

    /**
     * @var IssueStatus
     */
    private $status;

    /**
     * @var IssuePosition|null
     */
    private $position;

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): void
    {
        $this->number = $number;
    }

    public function getIsMarked(): bool
    {
        return $this->isMarked;
    }

    public function setIsMarked(bool $isMarked): void
    {
        $this->isMarked = $isMarked;
    }

    public function getWasAddedWithClient(): bool
    {
        return $this->wasAddedWithClient;
    }

    public function setWasAddedWithClient(bool $wasAddedWithClient): void
    {
        $this->wasAddedWithClient = $wasAddedWithClient;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCraftsman(): ?string
    {
        return $this->craftsman;
    }

    public function setCraftsman(?string $craftsman): void
    {
        $this->craftsman = $craftsman;
    }

    public function getStatus(): IssueStatus
    {
        return $this->status;
    }

    public function setStatus(IssueStatus $status): void
    {
        $this->status = $status;
    }

    public function getPosition(): ?IssuePosition
    {
        return $this->position;
    }

    public function setPosition(?IssuePosition $position): void
    {
        $this->position = $position;
    }

    public function getMap(): string
    {
        return $this->map;
    }

    public function setMap(string $map): void
    {
        $this->map = $map;
    }

    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(?File $image): void
    {
        $this->image = $image;
    }
}
