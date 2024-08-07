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
 *      denormalizationContext={"groups"={}},
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

    #[Assert\NotBlank]
    #[Groups(['protocol-entry-read', 'protocol-entry-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[Assert\NotBlank]
    #[Groups(['protocol-entry-read', 'protocol-entry-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING)]
    private ?string $createdBy = null;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTime();
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
}
