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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentDateTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $visitedDateTime;

    /**
     * @return string
     */
    public function getReceiver(): string
    {
        return $this->receiver;
    }

    /**
     * @param string $receiver
     */
    public function setReceiver(string $receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return int
     */
    public function getEmailType(): int
    {
        return $this->emailType;
    }

    /**
     * @param int $emailType
     */
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

    /**
     * @param string $body
     */
    public function setBody(string $body)
    {
        $this->body = $body;
    }

    /**
     * @return string|null
     */
    public function getActionText(): ?string
    {
        return $this->actionText;
    }

    /**
     * @param string $actionText
     */
    public function setActionText(string $actionText)
    {
        $this->actionText = $actionText;
    }

    /**
     * @return string|null
     */
    public function getActionLink(): ?string
    {
        return $this->actionLink;
    }

    /**
     * @param string $actionLink
     */
    public function setActionLink(string $actionLink)
    {
        $this->actionLink = $actionLink;
    }

    /**
     * @return \DateTime|null
     */
    public function getSentDateTime(): ?\DateTime
    {
        return $this->sentDateTime;
    }

    /**
     * @param \DateTime $sentDateTime
     */
    public function setSentDateTime(\DateTime $sentDateTime)
    {
        $this->sentDateTime = $sentDateTime;
    }

    /**
     * @return \DateTime|null
     */
    public function getVisitedDateTime(): ?\DateTime
    {
        return $this->visitedDateTime;
    }

    /**
     * @param \DateTime $visitedDateTime
     */
    public function setVisitedDateTime(\DateTime $visitedDateTime)
    {
        $this->visitedDateTime = $visitedDateTime;
    }
}
