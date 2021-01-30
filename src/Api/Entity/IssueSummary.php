<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity;

class IssueSummary
{
    /**
     * @var int
     */
    private $openCount = 0;

    /**
     * @var int
     */
    private $resolvedCount = 0;

    /**
     * @var int
     */
    private $closedCount = 0;

    public static function fromArray(int $openCount, int $resolvedCount, int $closedCount): self
    {
        $self = new self();

        $self->openCount = $openCount;
        $self->resolvedCount = $resolvedCount;
        $self->closedCount = $closedCount;

        return $self;
    }

    public function getOpenCount(): int
    {
        return $this->openCount;
    }

    public function setOpenCount(int $openCount): void
    {
        $this->openCount = $openCount;
    }

    public function getResolvedCount(): int
    {
        return $this->resolvedCount;
    }

    public function setResolvedCount(int $resolvedCount): void
    {
        $this->resolvedCount = $resolvedCount;
    }

    public function getClosedCount(): int
    {
        return $this->closedCount;
    }

    public function setClosedCount(int $closedCount): void
    {
        $this->closedCount = $closedCount;
    }
}
