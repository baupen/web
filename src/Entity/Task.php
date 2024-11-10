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
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;
use App\Api\Filters\PatchedOrderFilter;
use App\Api\Filters\RequiredExactSearchFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A task helps to remember something to do on the level of construction site.
 *
 * @ApiResource(
 *      collectionOperations={
 *       "get",
 *       "post" = {"security_post_denormalize" = "is_granted('TASK_MODIFY', object)", "denormalization_context"={"groups"={"task-create", "task-write"}}},
 *      },
 *      itemOperations={
 *       "get" = {"security" = "is_granted('TASK_VIEW', object)"},
 *       "patch" = {"security" = "is_granted('TASK_MODIFY', object)"},
 *       "delete" = {"security" = "is_granted('TASK_MODIFY', object)"},
 *      },
 *      denormalizationContext={"groups"={"task-write"}},
 *      normalizationContext={"groups"={"task-read"}, "skip_null_values"=false}
 *  )
 *
 * @ApiFilter(RequiredExactSearchFilter::class, properties={"constructionSite"})
 * @ApiFilter(DateFilter::class, properties={"createdAt", "deadline", "closedAt",})
 * @ApiFilter(ExistsFilter::class, properties={"closedAt"})
 * @ApiFilter(PatchedOrderFilter::class, properties={"deadline"={"nulls_comparison": PatchedOrderFilter::NULLS_ALWAYS_LAST, "default_direction": "DESC"}, "createdAt": "ASC", "closedAt": "ASC"})
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Task extends BaseEntity implements ConstructionSiteOwnedEntityInterface
{
    use IdTrait;

    #[Assert\NotBlank]
    #[Groups(['task-create', 'task-read'])]
    #[ORM\ManyToOne(targetEntity: ConstructionSite::class, inversedBy: 'tasks')]
    private ?ConstructionSite $constructionSite = null;

    #[Assert\NotBlank]
    #[Groups(['task-read', 'task-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    private ?string $description = null;

    #[Groups(['task-read', 'task-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $deadline = null;

    /**
     * @var \DateTime|null
     */
    #[Assert\NotBlank]
    #[Groups(['task-read', 'task-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[Assert\NotBlank]
    #[Groups(['task-read', 'task-create'])]
    #[ORM\ManyToOne(targetEntity: ConstructionManager::class)]
    private ?ConstructionManager $createdBy = null;

    /**
     * @var \DateTime|null
     */
    #[Groups(['task-read', 'task-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
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
