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
use App\Api\Filters\RequiredExactSearchFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A reminder helps to remember something to do on the level of construction site.
 *
 * @ApiResource(
 *      collectionOperations={
 *       "get",
 *       "post" = {"security_post_denormalize" = "is_granted('REMINDER_MODIFY', object)", "denormalization_context"={"groups"={"reminder-create", "reminder-write"}}},
 *      },
 *      itemOperations={
 *       "get" = {"security" = "is_granted('REMINDER_VIEW', object)"},
 *       "patch" = {"security" = "is_granted('REMINDER_MODIFY', object)"},
 *       "delete" = {"security" = "is_granted('REMINDER_MODIFY', object)"},
 *      },
 *      denormalizationContext={"groups"={"reminder-write"}},
 *      normalizationContext={"groups"={"reminder-read"}, "skip_null_values"=false}
 *  )
 *
 * @ApiFilter(RequiredExactSearchFilter::class, properties={"constructionSite"})
 * @ApiFilter(DateFilter::class, properties={"createdAt", "closedAt"})
 * @ApiFilter(OrderFilter::class, properties={"createdAt": "ASC", "closedAt": "ASC"})
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Reminder extends BaseEntity implements ConstructionSiteOwnedEntityInterface
{
    use IdTrait;

    #[Assert\NotBlank]
    #[Groups(['reminder-create'])]
    #[ORM\ManyToOne(targetEntity: ConstructionSite::class, inversedBy: 'reminders')]
    private ?ConstructionSite $constructionSite = null;

    #[Assert\NotBlank]
    #[Groups(['reminder-read', 'reminder-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    private ?string $description = null;

    #[Groups(['reminder-read', 'reminder-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $deadline = null;

    /**
     * @var \DateTime|null
     */
    #[Assert\NotBlank]
    #[Groups(['reminder-read', 'reminder-create'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[Assert\NotBlank]
    #[Groups(['reminder-read', 'reminder-create'])]
    #[ORM\ManyToOne(targetEntity: ConstructionManager::class)]
    private ?ConstructionManager $createdBy = null;

    /**
     * @var \DateTime|null
     */
    #[Groups(['reminder-read', 'reminder-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $closedAt = null;

    #[Groups(['reminder-read', 'reminder-write'])]
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
