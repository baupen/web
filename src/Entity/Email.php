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

    public const TYPE_REGISTER_CONFIRM = 1;
    public const TYPE_RECOVER_CONFIRM = 2;
    public const TYPE_APP_INVITATION = 3;
    public const TYPE_CRAFTSMAN_ISSUE_REMINDER = 4;

    /**
     * @var string
     *
     * @ORM\Column(type="guid")
     */
    private $identifier;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $body;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $sentAt;

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

    public static function create(int $emailType, ConstructionManager $sentBy, ?string $body = null)
    {
        $email = new Email();

        $email->identifier = UuidV4::v4();
        $email->type = $emailType;
        $email->body = $body;
        $email->sentBy = $sentBy;
        $email->sentAt = new \DateTime();

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

    public function getType(): int
    {
        return $this->type;
    }

    public function getSentAt(): DateTime
    {
        return $this->sentAt;
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
        return ['sentBy' => $this->sentBy, 'identifier' => $this->identifier, 'emailType' => $this->type, 'body' => $this->body];
    }
}
