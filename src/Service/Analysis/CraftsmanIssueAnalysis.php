<?php

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
