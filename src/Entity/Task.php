<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\OrderFilterInterface;
use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\IriFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Api\Filters\PatchedOrderFilter;
use App\Api\Filters\RequiredExactSearchFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Traits\IdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['task:read']],
    denormalizationContext: ['groups' => ['task:write']]
)]
#[GetCollection(
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
class Task extends BaseEntity implements ConstructionSiteOwnedEntityInterface
{
    use IdTrait;

    #[Assert\NotBlank]
    #[Groups(['task-create', 'task-read'])]
    #[ORM\ManyToOne(targetEntity: ConstructionSite::class, inversedBy: 'tasks')]
    private ?ConstructionSite $constructionSite = null;

    #[Assert\NotBlank]
    #[Groups(['task-read', 'task-write'])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[Groups(['task-read', 'task-write'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $deadline = null;

    /**
     * @var \DateTime|null
     */
    #[Assert\NotBlank]
    #[Groups(['task-read', 'task-create'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[Assert\NotBlank]
    #[Groups(['task-read', 'task-create'])]
    #[ORM\ManyToOne(targetEntity: ConstructionManager::class)]
    private ?ConstructionManager $createdBy = null;

    /**
     * @var \DateTime|null
     */
    #[Groups(['task-read', 'task-write'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $closedAt = null;

    #[Groups(['task-read', 'task-write'])]
    #[ORM\ManyToOne(targetEntity: ConstructionManager::class)]
    private ?ConstructionManager $closedBy = null;

    public function getConstructionSite(): ConstructionSite
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

    public function getDeadline(): ?\DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTime $deadline): void
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): void
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

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeInterface $closedAt): void
    {
        $this->closedAt = $closedAt;
    }

    public function isConstructionSiteSet(): bool
    {
        return null !== $this->constructionSite;
    }
}
