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

class Summary
{
    /**
     * @var int
     */
    private $openCount;

    /**
     * @var int
     */
    private $overdueCount;

    /**
     * @var int
     */
    private $resolvedCount;

    /**
     * @var int
     */
    private $closedCount;

    public function getOpenCount(): int
    {
        return $this->openCount;
    }

    public function setOpenCount(int $openCount): void
    {
        $this->openCount = $openCount;
    }

    public function getOverdueCount(): int
    {
        return $this->overdueCount;
    }

    public function setOverdueCount(int $overdueCount): void
    {
        $this->overdueCount = $overdueCount;
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
