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
use Symfony\Component\Uid\UuidV4;

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
     * @ORM\Column(type="guid")
     */
    private $identifier;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $emailType = EmailType::REGISTER_CONFIRM;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $sentDateTime;

    /**
     * @var ConstructionManager
     *
     * @ORM\ManyToOne (targetEntity="App\Entity\ConstructionManager")
     */
    private $sentBy;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $readAt;

    public static function create(int $emailType, ConstructionManager $sentBy)
    {
        $email = new Email();

        $email->identifier = UuidV4::v4();
        $email->emailType = $emailType;
        $email->sentBy = $sentBy;
        $email->sentDateTime = new \DateTime();

        return $email;
    }

    public function markRead()
    {
        $this->readAt = new \DateTime();
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getEmailType(): int
    {
        return $this->emailType;
    }

    public function getSentDateTime(): DateTime
    {
        return $this->sentDateTime;
    }

    public function getSentBy(): ConstructionManager
    {
        return $this->sentBy;
    }

    public function getReadAt(): ?DateTime
    {
        return $this->readAt;
    }

    public function getContext(): array
    {
        return ['sentBy' => $this->sentBy, 'identifier' => $this->identifier];
    }
}
