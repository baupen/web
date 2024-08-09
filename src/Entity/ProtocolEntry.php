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

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filters\IsDeletedFilter;
use App\Api\Filters\RequiredExactSearchFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Enum\ProtocolEntryTypes;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A protocol entry adds context to the linked entity.
 *
 * @ApiResource(
 *      collectionOperations={
 *       "get",
 *       "post" = {"security_post_denormalize" = "is_granted('PROTOCOL_ENTRY_MODIFY', object)", "denormalization_context"={"groups"={"protocol-entry-create"}}},
 *      },
 *      itemOperations={
 *       "get" = {"security" = "is_granted('PROTOCOL_ENTRY_VIEW', object)"},
 *       "delete" = {"security" = "is_granted('PROTOCOL_ENTRY_MODIFY', object)"},
 *      },
 *      denormalizationContext={"groups"={"protocol-entry-create"}},
 *      normalizationContext={"groups"={"protocol-entry-read"}, "skip_null_values"=false}
 *  )
 *
 * @ApiFilter(RequiredExactSearchFilter::class, properties={"constructionSite"})
 * @ApiFilter(DateFilter::class, properties={"createdAt"})
 * @ApiFilter(SearchFilter::class, properties={"root": "exact"})
 * @ApiFilter(OrderFilter::class, properties={"createdAt": "ASC"})
 * @ApiFilter(IsDeletedFilter::class, properties={"isDeleted"})
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class ProtocolEntry extends BaseEntity implements ConstructionSiteOwnedEntityInterface
{
    use IdTrait;
    use SoftDeleteTrait;

    public const ISSUE_STATE_CREATED_TEXT = 'CREATED';
    public const ISSUE_STATE_REGISTERED_TEXT = 'REGISTERED';
    public const ISSUE_STATE_RESOLVED_TEXT = 'RESOLVED';
    public const ISSUE_STATE_CLOSED_TEXT = 'CLOSED';

    public const EMAIL_TYPE_CRAFTSMAN_ISSUE_REMINDER = 'CRAFTSMAN_ISSUE_REMINDER';

    #[Assert\NotBlank]
    #[Groups(['protocol-entry-create'])]
    #[ORM\ManyToOne(targetEntity: ConstructionSite::class, inversedBy: 'protocolEntries')]
    private ?ConstructionSite $constructionSite = null;

    #[Assert\NotBlank]
    #[Groups(['protocol-entry-read', 'protocol-entry-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING)]
    private ?string $root = null;

    #[Assert\NotBlank]
    #[Groups(['protocol-entry-read', 'protocol-entry-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, enumType: ProtocolEntryTypes::class)]
    private ProtocolEntryTypes $type = ProtocolEntryTypes::Text;

    #[Groups(['protocol-entry-read', 'protocol-entry-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, nullable: true)]
    private ?string $payload = null;

    /**
     * @var \DateTime|null
     */
    #[Assert\NotBlank]
    #[Groups(['protocol-entry-read', 'protocol-entry-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[Assert\NotBlank]
    #[Groups(['protocol-entry-read', 'protocol-entry-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING)]
    private ?string $createdBy = null;

    /**
     * @return ProtocolEntry[]
     */
    public static function createFromChangedIssue(?array $previousState, Issue $current, string $authority): array
    {
        $entries = [];

        $createEntry = function () use ($current, $authority) {
            $entry = new self();
            $entry->setConstructionSite($current->getConstructionSite());
            $entry->setRoot($current->getId());
            $entry->setCreatedBy($authority);
            $entry->setCreatedAt(new \DateTime());

            return $entry;
        };

        if (!$previousState || null === $previousState['createdAt']) {
            $entry = $createEntry();
            $entry->setPayload(self::ISSUE_STATE_CREATED_TEXT);
            $entry->setType(ProtocolEntryTypes::StatusSet);

            $entries[] = $entry;
        }

        if ((!$previousState || null === $previousState['registeredAt']) && $current->getRegisteredAt()) {
            $entry = $createEntry();
            $entry->setPayload(self::ISSUE_STATE_REGISTERED_TEXT);
            $entry->setType(ProtocolEntryTypes::StatusSet);

            $entries[] = $entry;
        }

        // state may be removed or changed
        if ($previousState && ($previousState['resolvedAt'] != $current->getResolvedAt())) {
            $entry = $createEntry();
            $entry->setPayload(self::ISSUE_STATE_RESOLVED_TEXT);
            $entry->setType($current->getResolvedAt() ? ProtocolEntryTypes::StatusSet : ProtocolEntryTypes::StatusUnset);
            $entry->setCreatedBy($current->getResolvedBy() ? $current->getResolvedBy()->getId() : $authority);

            $entries[] = $entry;
        }

        // state may be removed or changed
        if ($previousState && ($previousState['closedAt'] != $current->getClosedAt())) {
            $entry = $createEntry();
            $entry->setPayload(self::ISSUE_STATE_CLOSED_TEXT);
            $entry->setType($current->getClosedAt() ? ProtocolEntryTypes::StatusSet : ProtocolEntryTypes::StatusUnset);

            $entries[] = $entry;
        }

        return $entries;
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    public function getRoot(): string
    {
        return $this->root;
    }

    public function setRoot(string $root): void
    {
        $this->root = $root;
    }

    public function getType(): ProtocolEntryTypes
    {
        return $this->type;
    }

    public function setType(ProtocolEntryTypes $type): void
    {
        $this->type = $type;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function setPayload(?string $payload): void
    {
        $this->payload = $payload;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function isConstructionSiteSet(): bool
    {
        return null !== $this->constructionSite;
    }

    #[Groups(['protocol-entry-read'])]
    public function getIsDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
