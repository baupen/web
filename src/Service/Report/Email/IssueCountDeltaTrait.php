<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Email;

trait IssueCountDeltaTrait
{
    private int $openCountDelta;

    private int $resolvedCountDelta;

    private int $closedCountDelta;

    public function getOpenCountDelta(): int
    {
        return $this->openCountDelta;
    }

    public function setOpenCountDelta(int $openCountDelta): void
    {
        $this->openCountDelta = $openCountDelta;
    }

    public function getResolvedCountDelta(): int
    {
        return $this->resolvedCountDelta;
    }

    public function setResolvedCountDelta(int $resolvedCountDelta): void
    {
        $this->resolvedCountDelta = $resolvedCountDelta;
    }

    public function getClosedCountDelta(): int
    {
        return $this->closedCountDelta;
    }

    public function setClosedCountDelta(int $closedCountDelta): void
    {
        $this->closedCountDelta = $closedCountDelta;
    }
}
