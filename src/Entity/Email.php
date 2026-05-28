<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Enum\EmailType;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(type: Types::GUID)]
    private ?string $identifier = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $body = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $jsonBody = null;

    #[ORM\Column(type: Types::INTEGER, enumType: EmailType::class)]
    private ?EmailType $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $sentAt = null;

    #[ORM\ManyToOne(targetEntity: ConstructionManager::class)]
    private ?ConstructionManager $sentBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $readAt = null;

    public static function create(EmailType $emailType, ConstructionManager $sentBy, ?string $link = null, ?string $body = null, bool $jsonBody = false): Email
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

    public function getType(): EmailType
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

        return ['sentBy' => $this->sentBy, 'identifier' => $this->identifier, 'emailType' => $this->type->value, 'body' => $body, 'jsonBody' => $this->jsonBody, 'link' => $this->link];
    }
}
