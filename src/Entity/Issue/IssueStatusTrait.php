<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Issue;

use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait IssueStatusTrait
{
    /**
     * @Assert\NotBlank()
     *
     * @Groups({"issue-read", "issue-create"})
     *
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @Assert\NotBlank()
     *
     * @Groups({"issue-read", "issue-create"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private ?ConstructionManager $createdBy = null;

    /**
     * @var \DateTime|null
     *
     * @Assert\NotBlank(groups={"after-register"})
     *
     * @Groups({"issue-read", "issue-write"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $registeredAt = null;

    /**
     * @Assert\NotBlank(groups={"after-register"})
     *
     * @Groups({"issue-read", "issue-write"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private ?ConstructionManager $registeredBy = null;

    /**
     * @var \DateTime|null
     *
     * @Groups({"issue-read", "issue-write", "issue-craftsman-write"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $resolvedAt = null;

    /**
     * @Groups({"issue-read", "issue-write", "issue-craftsman-write"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Craftsman", inversedBy="resolvedIssues")
     */
    private ?Craftsman $resolvedBy = null;

    /**
     * @var \DateTime|null
     *
     * @Groups({"issue-read", "issue-write"})
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $closedAt = null;

    /**
     * @Groups({"issue-read", "issue-write"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private ?ConstructionManager $closedBy = null;

    /**
     * @Assert\Callback
     */
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

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
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

    public function getRegisteredAt(): ?\DateTime
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(?\DateTime $registeredAt): void
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

    public function getResolvedAt(): ?\DateTime
    {
        return $this->resolvedAt;
    }

    public function setResolvedAt(?\DateTime $resolvedAt): void
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

    public function getClosedAt(): ?\DateTime
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTime $closedAt): void
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
