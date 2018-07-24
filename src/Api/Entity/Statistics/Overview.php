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

    /**
     * @return int
     */
    public function getOpenIssuesCount(): int
    {
        return $this->openIssuesCount;
    }

    /**
     * @param int $openIssuesCount
     */
    public function setOpenIssuesCount(int $openIssuesCount): void
    {
        $this->openIssuesCount = $openIssuesCount;
    }

    /**
     * @return int
     */
    public function getOverdueIssuesCount(): int
    {
        return $this->overdueIssuesCount;
    }

    /**
     * @param int $overdueIssuesCount
     */
    public function setOverdueIssuesCount(int $overdueIssuesCount): void
    {
        $this->overdueIssuesCount = $overdueIssuesCount;
    }

    /**
     * @return int
     */
    public function getRespondedNotReviewedIssuesCount(): int
    {
        return $this->respondedNotReviewedIssuesCount;
    }

    /**
     * @param int $respondedNotReviewedIssuesCount
     */
    public function setRespondedNotReviewedIssuesCount(int $respondedNotReviewedIssuesCount): void
    {
        $this->respondedNotReviewedIssuesCount = $respondedNotReviewedIssuesCount;
    }

    /**
     * @return int
     */
    public function getMarkedIssuesCount(): int
    {
        return $this->markedIssuesCount;
    }

    /**
     * @param int $markedIssuesCount
     */
    public function setMarkedIssuesCount(int $markedIssuesCount): void
    {
        $this->markedIssuesCount = $markedIssuesCount;
    }

    /**
     * @return int
     */
    public function getNewIssuesCount(): int
    {
        return $this->newIssuesCount;
    }

    /**
     * @param int $newIssuesCount
     */
    public function setNewIssuesCount(int $newIssuesCount): void
    {
        $this->newIssuesCount = $newIssuesCount;
    }
}
