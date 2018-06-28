<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity\Dispatch;

class Craftsman extends \App\Api\Entity\Base\Craftsman
{
    /**
     * @var int
     */
    private $unreadIssuesCount;

    /**
     * @var int
     */
    private $openIssuesCount;

    /**
     * @var \DateTime|null
     */
    private $nextAnswerLimit;

    /**
     * @var \DateTime|null
     */
    private $lastEmailSent;

    /**
     * @var \DateTime|null
     */
    private $lastOnlineVisit;

    /**
     * @return int
     */
    public function getUnreadIssuesCount(): int
    {
        return $this->unreadIssuesCount;
    }

    /**
     * @param int $unreadIssuesCount
     */
    public function setUnreadIssuesCount(int $unreadIssuesCount): void
    {
        $this->unreadIssuesCount = $unreadIssuesCount;
    }

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
     * @return \DateTime|null
     */
    public function getNextAnswerLimit(): ?\DateTime
    {
        return $this->nextAnswerLimit;
    }

    /**
     * @param \DateTime|null $nextAnswerLimit
     */
    public function setNextAnswerLimit(?\DateTime $nextAnswerLimit): void
    {
        $this->nextAnswerLimit = $nextAnswerLimit;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastEmailSent(): ?\DateTime
    {
        return $this->lastEmailSent;
    }

    /**
     * @param \DateTime|null $lastEmailSent
     */
    public function setLastEmailSent(?\DateTime $lastEmailSent): void
    {
        $this->lastEmailSent = $lastEmailSent;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastOnlineVisit(): ?\DateTime
    {
        return $this->lastOnlineVisit;
    }

    /**
     * @param \DateTime|null $lastOnlineVisit
     */
    public function setLastOnlineVisit(?\DateTime $lastOnlineVisit): void
    {
        $this->lastOnlineVisit = $lastOnlineVisit;
    }
}
