<?php

namespace App\Entity\Issue;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Validator\CloseToNowWhenPreviouslyNull;
use App\Validator\MatchesIssueCraftsman;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait IssueStatusTrait
{
    #[Assert\NotBlank]
    #[Groups(['issue:read', 'issue:create'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Assert\NotBlank]
    #[Groups(['issue:read', 'issue:create'])]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\ManyToOne(targetEntity: ConstructionManager::class)]
    private ?ConstructionManager $createdBy = null;

    #[Assert\NotBlank(groups: ['after-register'])]
    #[Groups(['issue:read', 'issue:write'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $registeredAt = null;

    #[Assert\NotBlank(groups: ['after-register'])]
    #[Groups(['issue:read', 'issue:write'])]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\ManyToOne(targetEntity: ConstructionManager::class)]
    private ?ConstructionManager $registeredBy = null;

    #[Groups(['issue:read', 'issue:write', 'issue:write-craftsman'])]
    #[CloseToNowWhenPreviouslyNull]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $resolvedAt = null;

    #[Groups(['issue:read', 'issue:write', 'issue:write-craftsman'])]
    #[MatchesIssueCraftsman]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\ManyToOne(targetEntity: Craftsman::class, inversedBy: 'resolvedIssues')]
    private ?Craftsman $resolvedBy = null;

    #[Groups(['issue:read', 'issue:write'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $closedAt = null;

    #[Groups(['issue:read', 'issue:write'])]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\ManyToOne(targetEntity: ConstructionManager::class)]
    private ?ConstructionManager $closedBy = null;

    #[Assert\Callback]
    public function validateStatus(ExecutionContextInterface $context): void
    {
        if ((null === $this->registeredAt) !== (null === $this->registeredBy)) {
            $context->buildViolation('registeredAt and registeredBy must both be set or both null.')->addViolation();
        }

        if ((null === $this->resolvedAt) !== (null === $this->resolvedBy)) {
            $context->buildViolation('resolvedAt and resolvedBy must both be set or both null.')->addViolation();
        }

        if ((null === $this->closedAt) !== (null === $this->closedBy)) {
            $context->buildViolation('closedAt and closedBy must both be set or both null.')->addViolation();
        }
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedBy(): ConstructionManager
    {
        return $this->createdBy;
    }

    public function setCreatedBy(ConstructionManager $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getRegisteredAt(): ?\DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(?\DateTimeImmutable $registeredAt): void
    {
        $this->registeredAt = $registeredAt;
    }

    public function getRegisteredBy(): ?ConstructionManager
    {
        return $this->registeredBy;
    }

    public function setRegisteredBy(?ConstructionManager $registeredBy): void
    {
        $this->registeredBy = $registeredBy;
    }

    public function getResolvedAt(): ?\DateTimeImmutable
    {
        return $this->resolvedAt;
    }

    public function setResolvedAt(?\DateTimeImmutable $resolvedAt): void
    {
        $this->resolvedAt = $resolvedAt;
    }

    public function getResolvedBy(): ?Craftsman
    {
        return $this->resolvedBy;
    }

    public function setResolvedBy(?Craftsman $resolvedBy): void
    {
        $this->resolvedBy = $resolvedBy;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeImmutable $closedAt): void
    {
        $this->closedAt = $closedAt;
    }

    public function getClosedBy(): ?ConstructionManager
    {
        return $this->closedBy;
    }

    public function setClosedBy(?ConstructionManager $closedBy): void
    {
        $this->closedBy = $closedBy;
    }
}
