<?php

namespace App\Entity;

use App\Api\Filters\IsDeletedFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\TimeTrait;
use App\Enum\EmailType;
use App\Enum\IssueEventTypes;
use App\Enum\IssueState;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An issue event adds context to the linked entity.
 *
 * @ApiResource(
 *      collectionOperations={
 *       "get",
 *       "post" = {"security_post_denormalize" = "is_granted('ISSUE_EVENT_CREATE', object)", "denormalization_context"={"groups"={"issue-event:create", "issue-event:write"}}},
 *      },
 *      itemOperations={
 *       "get" = {"security" = "is_granted('ISSUE_EVENT_VIEW', object)"},
 *       "patch" = {"security" = "is_granted('ISSUE_EVENT_MODIFY', object)"},
 *       "delete" = {"security" = "is_granted('ISSUE_EVENT_DELETE', object)"},
 *      },
 *      denormalizationContext={"groups"={"issue-event:write"}},
 *      normalizationContext={"groups"={"issue-event:read"}, "skip_null_values"=false}
 *  )
 *
 * @ApiFilter(RequiredExactSearchFilter::class, properties={"constructionSite","createdBy"})
 * @ApiFilter(DateFilter::class, properties={"createdAt"})
 * @ApiFilter(SearchFilter::class, properties={"root": "exact"})
 * @ApiFilter(BooleanFilter::class, properties={"contextualForChildren"})
 * @ApiFilter(OrderFilter::class, properties={"createdAt": "ASC"})
 * @ApiFilter(IsDeletedFilter::class, properties={"isDeleted"})
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[\ApiPlatform\Metadata\ApiResource(
    denormalizationContext: ['groups' => ['issue-event:write']],
    normalizationContext: ['groups' => ['issue-event:read', 'time:read', 'soft-delete:read']],
)]
class IssueEvent extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use SoftDeleteTrait;

    #[Assert\NotBlank]
    #[Groups(['issue-event:create'])]
    #[ORM\ManyToOne(targetEntity: ConstructionSite::class, inversedBy: 'issueEvents')]
    private ?ConstructionSite $constructionSite = null;

    #[Assert\NotBlank]
    #[Groups(['issue-event:read', 'issue-event:create'])]
    #[ORM\Column(type: Types::STRING)]
    private ?string $root = null;

    #[Assert\NotBlank]
    #[Groups(['issue-event:read', 'issue-event:create'])]
    #[ORM\Column(type: Types::STRING, enumType: IssueEventTypes::class)]
    private IssueEventTypes $type = IssueEventTypes::Text;

    #[Assert\NotBlank]
    #[Groups(['issue-event:read', 'issue-event:create'])]
    #[ORM\Column(type: Types::STRING)]
    private ?string $createdBy = null;

    #[Assert\NotBlank]
    #[Groups(['issue-event:read', 'issue-event:write'])]
    #[ORM\Column(type: Types::STRING)]
    private ?string $lastChangedBy = null;

    #[Groups(['issue-event:read', 'issue-event:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $payload = null;

    #[Assert\NotBlank]
    #[Groups(['issue-event:read', 'issue-event:write'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $timestamp = null;

    #[Groups(['issue-event:read', 'issue-event:write'])]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private ?bool $contextualForChildren = true;

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
            $entry->setTimestamp(new \DateTimeImmutable());
            $entry->setCreatedBy($authority);
            $entry->setLastChangedBy($authority);

            return $entry;
        };

        if (!$previousState || null === $previousState['createdAt']) {
            $entry = $createEntry();
            $entry->setPayload(IssueState::CREATED->name);
            $entry->setType(IssueEventTypes::StatusSet);

            $entries[] = $entry;
        }

        if ((!$previousState || null === $previousState['registeredAt']) && $current->getRegisteredAt()) {
            $entry = $createEntry();
            $entry->setPayload(IssueState::REGISTERED->name);
            $entry->setType(IssueEventTypes::StatusSet);

            $entries[] = $entry;
        }

        // state may be removed or changed
        if ($previousState && ($previousState['resolvedAt'] != $current->getResolvedAt())) {
            $entry = $createEntry();
            $entry->setPayload(IssueState::RESOLVED->name);
            $entry->setType($current->getResolvedAt() ? IssueEventTypes::StatusSet : IssueEventTypes::StatusUnset);
            $createdBy = $current->getResolvedBy() ? $current->getResolvedBy()->getId() : $authority;
            $entry->setCreatedBy($createdBy);
            $entry->setLastChangedBy($createdBy);

            $entries[] = $entry;
        }

        // state may be removed or changed
        if ($previousState && ($previousState['closedAt'] != $current->getClosedAt())) {
            $entry = $createEntry();
            $entry->setPayload(IssueState::CLOSED->name);
            $entry->setType($current->getClosedAt() ? IssueEventTypes::StatusSet : IssueEventTypes::StatusUnset);

            $entries[] = $entry;
        }

        return $entries;
    }

    public static function createFromEmail(ConstructionSite $constructionSite, string $receiver, string $authority, array $payload): self
    {
        // add event
        $issueEvent = new IssueEvent();
        $issueEvent->setConstructionSite($constructionSite);
        $issueEvent->setRoot($receiver);
        $issueEvent->setCreatedBy($authority);
        $issueEvent->setLastChangedBy($authority);
        $issueEvent->setTimestamp(new \DateTimeImmutable());
        $issueEvent->setType(IssueEventTypes::Email);
        $issueEvent->setPayload(json_encode($payload));
        return  $issueEvent;
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

    public function getTimestamp(): ?\DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setTimestamp(?\DateTimeImmutable $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getContextualForChildren(): ?bool
    {
        return $this->contextualForChildren;
    }

    public function setContextualForChildren(?bool $contextualForChildren): void
    {
        $this->contextualForChildren = $contextualForChildren;
    }

    public function getFile(): ?IssueEventFile
    {
        return $this->file;
    }

    public function setFile(?IssueEventFile $file): void
    {
        $this->file = $file;
    }
}
