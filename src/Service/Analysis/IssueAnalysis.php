<?php

namespace App\Service\Analysis;

class IssueAnalysis
{
    use IssueCountAnalysisTrait;

    private int $newCount = 0;

    public function getNewCount(): int
    {
        return $this->newCount;
    }

    public function setNewCount(int $newCount): void
    {
        $this->newCount = $newCount;
    }
}
