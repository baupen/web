<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Craftsman;

use App\Entity\Craftsman;

class CurrentIssueState
{
    /**
     * @var int
     */
    private $notReadIssuesCount = 0;

    /**
     * @var int
     */
    private $recentlyReviewedIssuesCount = 0;

    /**
     * @var int
     */
    private $notRespondedIssuesCount = 0;

    /**
     * @var int
     */
    private $overdueIssuesCount = 0;

    /**
     * @var \DateTime|null
     */
    private $nextResponseLimit = null;

    /**
     * CurrentIssueState constructor.
     *
     * @param Craftsman $craftsman
     * @param $referenceTime
     */
    public function __construct(Craftsman $craftsman, $referenceTime)
    {
        $lastAction = $craftsman->getLastAction();
        foreach ($craftsman->getIssues() as $issue) {
            if ($issue->getRegisteredAt() !== null && $issue->getRespondedAt() === null) {
                if ($issue->getReviewedAt() === null) {
                    ++$this->notRespondedIssuesCount;
                    if ($lastAction === null || $issue->getRegisteredAt() > $lastAction) {
                        ++$this->notReadIssuesCount;
                    }

                    if ($issue->getResponseLimit() !== null) {
                        if ($issue->getResponseLimit() < $referenceTime) {
                            ++$this->overdueIssuesCount;
                        }

                        if ($this->nextResponseLimit === null || $issue->getResponseLimit() < $this->nextResponseLimit) {
                            $this->nextResponseLimit = $issue->getResponseLimit();
                        }
                    }
                } elseif ($lastAction === null || $issue->getReviewedAt() > $lastAction) {
                    ++$this->recentlyReviewedIssuesCount;
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getNotReadIssuesCount(): int
    {
        return $this->notReadIssuesCount;
    }

    /**
     * @return int
     */
    public function getRecentlyReviewedIssuesCount(): int
    {
        return $this->recentlyReviewedIssuesCount;
    }

    /**
     * @return int
     */
    public function getNotRespondedIssuesCount(): int
    {
        return $this->notRespondedIssuesCount;
    }

    /**
     * @return int
     */
    public function getOverdueIssuesCount(): int
    {
        return $this->overdueIssuesCount;
    }

    /**
     * @return \DateTime|null
     */
    public function getNextResponseLimit(): ?\DateTime
    {
        return $this->nextResponseLimit;
    }
}
