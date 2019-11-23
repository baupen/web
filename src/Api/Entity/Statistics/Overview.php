<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Statistics;

class Overview
{
    /**
     * @var int
     */
    private $newIssuesCount;

    /**
     * @var int
     */
    private $openIssuesCount;

    /**
     * @var int
     */
    private $overdueIssuesCount;

    /**
     * @var int
     */
    private $respondedNotReviewedIssuesCount;

    /**
     * @var int
     */
    private $markedIssuesCount;

    public function getOpenIssuesCount(): int
    {
        return $this->openIssuesCount;
    }

    public function setOpenIssuesCount(int $openIssuesCount): void
    {
        $this->openIssuesCount = $openIssuesCount;
    }

    public function getOverdueIssuesCount(): int
    {
        return $this->overdueIssuesCount;
    }

    public function setOverdueIssuesCount(int $overdueIssuesCount): void
    {
        $this->overdueIssuesCount = $overdueIssuesCount;
    }

    public function getRespondedNotReviewedIssuesCount(): int
    {
        return $this->respondedNotReviewedIssuesCount;
    }

    public function setRespondedNotReviewedIssuesCount(int $respondedNotReviewedIssuesCount): void
    {
        $this->respondedNotReviewedIssuesCount = $respondedNotReviewedIssuesCount;
    }

    public function getMarkedIssuesCount(): int
    {
        return $this->markedIssuesCount;
    }

    public function setMarkedIssuesCount(int $markedIssuesCount): void
    {
        $this->markedIssuesCount = $markedIssuesCount;
    }

    public function getNewIssuesCount(): int
    {
        return $this->newIssuesCount;
    }

    public function setNewIssuesCount(int $newIssuesCount): void
    {
        $this->newIssuesCount = $newIssuesCount;
    }
}
