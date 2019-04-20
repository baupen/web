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

use DateTime;

class Craftsman extends \App\Api\Entity\Base\Craftsman
{
    /**
     * @var int
     */
    private $notReadIssuesCount;

    /**
     * @var int
     */
    private $notRespondedIssuesCount;

    /**
     * @var DateTime|null
     */
    private $nextResponseLimit;

    /**
     * @var DateTime|null
     */
    private $lastEmailSent;

    /**
     * @var DateTime|null
     */
    private $lastOnlineVisit;

    /**
     * @var string
     */
    private $personalUrl;

    /**
     * @return int
     */
    public function getNotReadIssuesCount(): int
    {
        return $this->notReadIssuesCount;
    }

    /**
     * @param int $notReadIssuesCount
     */
    public function setNotReadIssuesCount(int $notReadIssuesCount): void
    {
        $this->notReadIssuesCount = $notReadIssuesCount;
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
     * @return DateTime|null
     */
    public function getNextResponseLimit(): ?DateTime
    {
        return $this->nextResponseLimit;
    }

    /**
     * @param DateTime|null $nextResponseLimit
     */
    public function setNextResponseLimit(?DateTime $nextResponseLimit): void
    {
        $this->nextResponseLimit = $nextResponseLimit;
    }

    /**
     * @return DateTime|null
     */
    public function getLastEmailSent(): ?DateTime
    {
        return $this->lastEmailSent;
    }

    /**
     * @param DateTime|null $lastEmailSent
     */
    public function setLastEmailSent(?DateTime $lastEmailSent): void
    {
        $this->lastEmailSent = $lastEmailSent;
    }

    /**
     * @return DateTime|null
     */
    public function getLastOnlineVisit(): ?DateTime
    {
        return $this->lastOnlineVisit;
    }

    /**
     * @param DateTime|null $lastOnlineVisit
     */
    public function setLastOnlineVisit(?DateTime $lastOnlineVisit): void
    {
        $this->lastOnlineVisit = $lastOnlineVisit;
    }

    /**
     * @return string
     */
    public function getPersonalUrl(): string
    {
        return $this->personalUrl;
    }

    /**
     * @param string $personalUrl
     */
    public function setPersonalUrl(string $personalUrl): void
    {
        $this->personalUrl = $personalUrl;
    }
}
