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

    public function getNotReadIssuesCount(): int
    {
        return $this->notReadIssuesCount;
    }

    public function setNotReadIssuesCount(int $notReadIssuesCount): void
    {
        $this->notReadIssuesCount = $notReadIssuesCount;
    }

    public function getNotRespondedIssuesCount(): int
    {
        return $this->notRespondedIssuesCount;
    }

    public function setNotRespondedIssuesCount(int $notRespondedIssuesCount): void
    {
        $this->notRespondedIssuesCount = $notRespondedIssuesCount;
    }

    public function getNextResponseLimit(): ?DateTime
    {
        return $this->nextResponseLimit;
    }

    public function setNextResponseLimit(?DateTime $nextResponseLimit): void
    {
        $this->nextResponseLimit = $nextResponseLimit;
    }

    public function getLastEmailSent(): ?DateTime
    {
        return $this->lastEmailSent;
    }

    public function setLastEmailSent(?DateTime $lastEmailSent): void
    {
        $this->lastEmailSent = $lastEmailSent;
    }

    public function getLastOnlineVisit(): ?DateTime
    {
        return $this->lastOnlineVisit;
    }

    public function setLastOnlineVisit(?DateTime $lastOnlineVisit): void
    {
        $this->lastOnlineVisit = $lastOnlineVisit;
    }

    public function getPersonalUrl(): string
    {
        return $this->personalUrl;
    }

    public function setPersonalUrl(string $personalUrl): void
    {
        $this->personalUrl = $personalUrl;
    }
}
