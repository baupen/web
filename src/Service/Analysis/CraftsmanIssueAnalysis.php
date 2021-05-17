<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Analysis;

class CraftsmanIssueAnalysis
{
    use IssueCountAnalysisTrait;

    private int $unreadCount = 0;

    private int $overdueCount = 0;

    public function getUnreadCount(): int
    {
        return $this->unreadCount;
    }

    public function setUnreadCount(int $unreadCount): void
    {
        $this->unreadCount = $unreadCount;
    }

    public function getOverdueCount(): int
    {
        return $this->overdueCount;
    }

    public function setOverdueCount(int $overdueCount): void
    {
        $this->overdueCount = $overdueCount;
    }
}
