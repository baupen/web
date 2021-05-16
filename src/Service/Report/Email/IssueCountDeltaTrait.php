<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Email;

trait IssueCountDeltaTrait
{
    private int $openCountDelta = 0;

    private int $resolvedCountDelta = 0;

    private int $closedCountDelta = 0;

    /**
     * @param IssueCountDeltaTrait $other
     */
    protected function addIssueCountDelta($other)
    {
        $this->openCountDelta += $other->getOpenCountDelta();
        $this->resolvedCountDelta += $other->getResolvedCountDelta();
        $this->closedCountDelta += $other->getClosedCountDelta();
    }

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
