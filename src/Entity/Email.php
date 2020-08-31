<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Enum\EmailType;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Email is a sent email to the specified receivers.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Email extends BaseEntity
{
    use IdTrait;

    const SENDER_SYSTEM = 'system';

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $senderName;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $senderEmail;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $receiver;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $actionText;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $actionLink;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $emailType = EmailType::TEXT_EMAIL;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentDateTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $visitedDateTime;

    public function setSender(string $senderName, string $senderEmail): void
    {
        $this->senderName = $senderName;
        $this->senderEmail = $senderEmail;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function getSenderEmail(): ?string
    {
        return $this->senderEmail;
    }

    public function getReceiver(): string
    {
        return $this->receiver;
    }

    public function setReceiver(string $receiver)
    {
        $this->receiver = $receiver;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    public function getEmailType(): int
    {
        return $this->emailType;
    }

    public function setEmailType(int $emailType)
    {
        $this->emailType = $emailType;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    public function setBody(string $body)
    {
        $this->body = $body;
    }

    public function getActionText(): ?string
    {
        return $this->actionText;
    }

    public function setActionText(string $actionText)
    {
        $this->actionText = $actionText;
    }

    public function getActionLink(): ?string
    {
        return $this->actionLink;
    }

    public function setActionLink(string $actionLink)
    {
        $this->actionLink = $actionLink;
    }

    public function getSentDateTime(): ?DateTime
    {
        return $this->sentDateTime;
    }

    public function setSentDateTime(DateTime $sentDateTime)
    {
        $this->sentDateTime = $sentDateTime;
    }

    public function getVisitedDateTime(): ?DateTime
    {
        return $this->visitedDateTime;
    }

    public function setVisitedDateTime(DateTime $visitedDateTime)
    {
        $this->visitedDateTime = $visitedDateTime;
    }
}
