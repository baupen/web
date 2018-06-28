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
    private $noteReadIssuesCount;

    /**
     * @var int
     */
    private $notRespondedIssuesCount;

    /**
     * @var \DateTime|null
     */
    private $nextResponseLimit;

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
    public function getNoteReadIssuesCount(): int
    {
        return $this->noteReadIssuesCount;
    }

    /**
     * @param int $noteReadIssuesCount
     */
    public function setNoteReadIssuesCount(int $noteReadIssuesCount): void
    {
        $this->noteReadIssuesCount = $noteReadIssuesCount;
    }

    /**
     * @return int
     */
    public function getNotRespondedIssuesCount(): int
    {
        return $this->notRespondedIssuesCount;
    }

    /**
     * @param int $notRespondedIssuesCount
     */
    public function setNotRespondedIssuesCount(int $notRespondedIssuesCount): void
    {
        $this->notRespondedIssuesCount = $notRespondedIssuesCount;
    }

    /**
     * @return \DateTime|null
     */
    public function getNextResponseLimit(): ?\DateTime
    {
        return $this->nextResponseLimit;
    }

    /**
     * @param \DateTime|null $nextResponseLimit
     */
    public function setNextResponseLimit(?\DateTime $nextResponseLimit): void
    {
        $this->nextResponseLimit = $nextResponseLimit;
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
