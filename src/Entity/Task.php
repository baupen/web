<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\OrderFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\IriFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Api\Provider\AuthenticatedCollectionProvider;
use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    denormalizationContext: ['groups' => ['task:write']],
    normalizationContext: ['groups' => ['task:read'], "skip_null_values" => false],
)]
#[GetCollection(
    provider: AuthenticatedCollectionProvider::class,
    security: "is_granted('ROLE_ASSOCIATED_CONSTRUCTION_MANAGER')",
    parameters: [
        'constructionSite' => new QueryParameter(filter: new IriFilter(),),
    ],
)]
#[Get(security: 'is_granted("TASK_VIEW", object)')]
#[Post(securityPostDenormalize: 'is_granted("TASK_MODIFY", object)', denormalizationContext: ['groups' => ['task:create', 'task:write']])]
#[Patch(security: 'is_granted("TASK_MODIFY", object)')]
#[Delete(security: 'is_granted("TASK_MODIFY", object)')]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'closedAt'], strategy: OrderFilterInterface::NULLS_ALWAYS_LAST)]
#[ApiFilter(DateFilter::class, properties: ['createdAt', "deadline", "closedAt"])]
#[ApiFilter(ExistsFilter::class, properties: ['closedAt'])]
class Task extends BaseEntity
{
    use IdTrait;

    #[Assert\NotBlank]
    #[Groups(['task:create', 'task:read'])]
    #[ORM\ManyToOne(targetEntity: ConstructionSite::class, inversedBy: 'tasks')]
    private ?ConstructionSite $constructionSite = null;

    #[Assert\NotBlank]
    #[Groups(['task:read', 'task:write'])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[Groups(['task:read', 'task:write'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deadline = null;

    #[Assert\NotBlank]
    #[Groups(['task:read', 'task:create'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Assert\NotBlank]
    #[Groups(['task:read', 'task:create'])]
    #[ORM\ManyToOne(targetEntity: ConstructionManager::class)]
    private ?ConstructionManager $createdBy = null;

    #[Groups(['task:read', 'task:write'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $closedAt = null;

    #[Groups(['task:read', 'task:write'])]
    #[ORM\ManyToOne(targetEntity: ConstructionManager::class)]
    private ?ConstructionManager $closedBy = null;

    public function getConstructionSite(): ?ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDeadline(): ?\DateTimeImmutable
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTimeImmutable $deadline): void
    {
        $this->deadline = $deadline;
    }

    public function getCreatedBy(): ?ConstructionManager
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?ConstructionManager $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getClosedBy(): ?ConstructionManager
    {
        return $this->closedBy;
    }

    public function setClosedBy(?ConstructionManager $closedBy): void
    {
        $this->closedBy = $closedBy;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeImmutable $closedAt): void
    {
        $this->closedAt = $closedAt;
    }
}
