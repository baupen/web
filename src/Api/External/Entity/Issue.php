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

    /**
     * @return int|null
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @param int|null $number
     */
    public function setNumber(?int $number): void
    {
        $this->number = $number;
    }

    /**
     * @return bool
     */
    public function getIsMarked(): bool
    {
        return $this->isMarked;
    }

    /**
     * @param bool $isMarked
     */
    public function setIsMarked(bool $isMarked): void
    {
        $this->isMarked = $isMarked;
    }

    /**
     * @return bool
     */
    public function getWasAddedWithClient(): bool
    {
        return $this->wasAddedWithClient;
    }

    /**
     * @param bool $wasAddedWithClient
     */
    public function setWasAddedWithClient(bool $wasAddedWithClient): void
    {
        $this->wasAddedWithClient = $wasAddedWithClient;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return null|string
     */
    public function getCraftsman(): ?string
    {
        return $this->craftsman;
    }

    /**
     * @param null|string $craftsman
     */
    public function setCraftsman(?string $craftsman): void
    {
        $this->craftsman = $craftsman;
    }

    /**
     * @return IssueStatus
     */
    public function getStatus(): IssueStatus
    {
        return $this->status;
    }

    /**
     * @param IssueStatus $status
     */
    public function setStatus(IssueStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return IssuePosition|null
     */
    public function getPosition(): ?IssuePosition
    {
        return $this->position;
    }

    /**
     * @param IssuePosition|null $position
     */
    public function setPosition(?IssuePosition $position): void
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getMap(): string
    {
        return $this->map;
    }

    /**
     * @param string $map
     */
    public function setMap(string $map): void
    {
        $this->map = $map;
    }

    /**
     * @return File|null
     */
    public function getImage(): ?File
    {
        return $this->image;
    }

    /**
     * @param File|null $image
     */
    public function setImage(?File $image): void
    {
        $this->image = $image;
    }
}
