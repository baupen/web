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
use App\Entity\Traits\TimeTrait;
use App\Enum\IssueEventTypes;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A issue event adds context to the linked entity.
 *
 * @ApiResource(
 *      collectionOperations={
 *       "get",
 *       "post" = {"security_post_denormalize" = "is_granted('ISSUE_EVENT_CREATE', object)", "denormalization_context"={"groups"={"issue-event-create", "issue-event-write"}}},
 *      },
 *      itemOperations={
 *       "get" = {"security" = "is_granted('ISSUE_EVENT_VIEW', object)"},
 *       "patch" = {"security" = "is_granted('ISSUE_EVENT_MODIFY', object)"},
 *       "delete" = {"security" = "is_granted('ISSUE_EVENT_DELETE', object)"},
 *      },
 *      denormalizationContext={"groups"={"issue-event-write"}},
 *      normalizationContext={"groups"={"issue-event-read"}, "skip_null_values"=false}
 *  )
 *
 * @ApiFilter(RequiredExactSearchFilter::class, properties={"constructionSite","createdBy"})
 * @ApiFilter(DateFilter::class, properties={"createdAt"})
 * @ApiFilter(SearchFilter::class, properties={"root": "exact"})
 * @ApiFilter(OrderFilter::class, properties={"createdAt": "ASC"})
 * @ApiFilter(IsDeletedFilter::class, properties={"isDeleted"})
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class IssueEvent extends BaseEntity implements ConstructionSiteOwnedEntityInterface
{
    use IdTrait;
    use TimeTrait;
    use SoftDeleteTrait;

    public const ISSUE_STATE_CREATED_TEXT = 'CREATED';
    public const ISSUE_STATE_REGISTERED_TEXT = 'REGISTERED';
    public const ISSUE_STATE_RESOLVED_TEXT = 'RESOLVED';
    public const ISSUE_STATE_CLOSED_TEXT = 'CLOSED';

    public const EMAIL_TYPE_CRAFTSMAN_ISSUE_REMINDER = 'CRAFTSMAN_ISSUE_REMINDER';

    #[Assert\NotBlank]
    #[Groups(['issue-event-create'])]
    #[ORM\ManyToOne(targetEntity: ConstructionSite::class, inversedBy: 'issueEvents')]
    private ?ConstructionSite $constructionSite = null;

    #[Assert\NotBlank]
    #[Groups(['issue-event-read', 'issue-event-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING)]
    private ?string $root = null;

    #[Assert\NotBlank]
    #[Groups(['issue-event-read', 'issue-event-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN, options: ['default' => true])]
    private ?bool $contextualForChildren = true;

    #[Assert\NotBlank]
    #[Groups(['issue-event-read', 'issue-event-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, enumType: IssueEventTypes::class)]
    private IssueEventTypes $type = IssueEventTypes::Text;

    #[Assert\NotBlank]
    #[Groups(['issue-event-read', 'issue-event-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING)]
    private ?string $createdBy = null;

    #[Assert\NotBlank]
    #[Groups(['issue-event-read', 'issue-event-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING)]
    private ?string $lastChangedBy = null;

    #[Groups(['issue-event-read', 'issue-event-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $payload = null;

    #[Assert\NotBlank]
    #[Groups(['issue-event-read', 'issue-event-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $timestamp = null;

    #[ORM\ManyToOne(targetEntity: IssueEventFile::class, cascade: ['persist', 'remove'])]
    private ?IssueEventFile $file = null;

    /**
     * @return IssueEvent[]
     */
    public static function createFromChangedIssue(?array $previousState, Issue $current, string $authority): array
    {
        $entries = [];

        $createEntry = function () use ($current, $authority) {
            $entry = new self();
            $entry->setConstructionSite($current->getConstructionSite());
            $entry->setRoot($current->getId());
            $entry->setTimestamp(new \DateTime());
            $entry->setCreatedBy($authority);
            $entry->setLastChangedBy($authority);

            return $entry;
        };

        if (!$previousState || null === $previousState['createdAt']) {
            $entry = $createEntry();
            $entry->setPayload(self::ISSUE_STATE_CREATED_TEXT);
            $entry->setType(IssueEventTypes::StatusSet);

            $entries[] = $entry;
        }

        if ((!$previousState || null === $previousState['registeredAt']) && $current->getRegisteredAt()) {
            $entry = $createEntry();
            $entry->setPayload(self::ISSUE_STATE_REGISTERED_TEXT);
            $entry->setType(IssueEventTypes::StatusSet);

            $entries[] = $entry;
        }

        // state may be removed or changed
        if ($previousState && ($previousState['resolvedAt'] != $current->getResolvedAt())) {
            $entry = $createEntry();
            $entry->setPayload(self::ISSUE_STATE_RESOLVED_TEXT);
            $entry->setType($current->getResolvedAt() ? IssueEventTypes::StatusSet : IssueEventTypes::StatusUnset);
            $createdBy = $current->getResolvedBy() ? $current->getResolvedBy()->getId() : $authority;
            $entry->setCreatedBy($createdBy);
            $entry->setLastChangedBy($createdBy);

            $entries[] = $entry;
        }

        // state may be removed or changed
        if ($previousState && ($previousState['closedAt'] != $current->getClosedAt())) {
            $entry = $createEntry();
            $entry->setPayload(self::ISSUE_STATE_CLOSED_TEXT);
            $entry->setType($current->getClosedAt() ? IssueEventTypes::StatusSet : IssueEventTypes::StatusUnset);

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

    public function getRoot(): ?string
    {
        return $this->root;
    }

    public function setRoot(?string $root): void
    {
        $this->root = $root;
    }

    public function getType(): ?IssueEventTypes
    {
        return $this->type;
    }

    public function setType(?IssueEventTypes $type): void
    {
        $this->type = $type;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getLastChangedBy(): ?string
    {
        return $this->lastChangedBy;
    }

    public function setLastChangedBy(?string $lastChangedBy): void
    {
        $this->lastChangedBy = $lastChangedBy;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function setPayload(?string $payload): void
    {
        $this->payload = $payload;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(?\DateTimeInterface $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getFile(): ?IssueEventFile
    {
        return $this->file;
    }

    public function setFile(?IssueEventFile $file): void
    {
        $this->file = $file;
    }

    public function isConstructionSiteSet(): bool
    {
        return null !== $this->constructionSite;
    }

    #[Groups(['issue-event-read'])]
    public function getIsDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    #[Groups(['issue-event-read'])]
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    #[Groups(['issue-event-read'])]
    public function getLastChangedAt(): \DateTimeInterface
    {
        return $this->lastChangedAt;
    }
}
