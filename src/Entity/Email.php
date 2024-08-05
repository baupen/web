<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV4;

/**
 * An Email is a sent email to the specified receivers.
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Email extends BaseEntity
{
    use IdTrait;

    public const TYPE_REGISTER_CONFIRM = 1;
    public const TYPE_RECOVER_CONFIRM = 2;
    public const TYPE_APP_INVITATION = 3;
    public const TYPE_CRAFTSMAN_ISSUE_REMINDER = 4;
    public const TYPE_CONSTRUCTION_SITES_OVERVIEW = 5;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::GUID)]
    private ?string $identifier = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $body = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN, options: ['default' => false])]
    private ?bool $jsonBody = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $type = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)]
    private ?\DateTime $sentAt = null;

    #[ORM\ManyToOne(targetEntity: ConstructionManager::class)]
    private ?ConstructionManager $sentBy = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $readAt = null;

    public static function create(int $emailType, ConstructionManager $sentBy, ?string $link = null, ?string $body = null, bool $jsonBody = false): Email
    {
        $email = new Email();

        $email->identifier = UuidV4::v4();
        $email->type = $emailType;
        $email->link = $link;
        $email->body = $body;
        $email->jsonBody = $jsonBody;
        $email->sentBy = $sentBy;
        $email->sentAt = new \DateTime();

        return $email;
    }

    public function markRead(): void
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

    public function getSentAt(): \DateTime
    {
        return $this->sentAt;
    }

    public function getSentBy(): ConstructionManager
    {
        return $this->sentBy;
    }

    public function getReadAt(): ?\DateTime
    {
        return $this->readAt;
    }

    public function getContext(): array
    {
        $body = $this->jsonBody ? json_decode($this->body) : $this->body;

        return ['sentBy' => $this->sentBy, 'identifier' => $this->identifier, 'emailType' => $this->type, 'body' => $body, 'jsonBody' => $this->jsonBody, 'link' => $this->link];
    }
}
