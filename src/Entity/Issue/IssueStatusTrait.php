<?php

/*
 * This file is part of the mangel.io project.
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
     * @var DateTime
     *
     * @Assert\NotBlank()
     * @Groups({"issue-read", "issue-create"})
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var ConstructionManager
     *
     * @Assert\NotBlank()
     * @Groups({"issue-read", "issue-create"})
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $createdBy;

    /**
     * @var DateTime|null
     *
     * @Assert\NotBlank(groups={"after-register"})
     * @Groups({"issue-read", "issue-write"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registeredAt;

    /**
     * @var ConstructionManager|null
     *
     * @Assert\NotBlank(groups={"after-register"})
     * @Groups({"issue-read", "issue-write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $registeredBy;

    /**
     * @var DateTime|null
     *
     * @Groups({"issue-read", "issue-write", "issue-craftsman-write"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resolvedAt;

    /**
     * @var Craftsman|null
     *
     * @Groups({"issue-read", "issue-write", "issue-craftsman-write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Craftsman", inversedBy="resolvedIssues")
     */
    private $resolvedBy;

    /**
     * @var DateTime|null
     *
     * @Groups({"issue-read", "issue-write"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closedAt;

    /**
     * @var ConstructionManager|null
     *
     * @Groups({"issue-read", "issue-write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $closedBy;

    /**
     * @Assert\Callback
     */
    public function validateStatus(ExecutionContextInterface $context)
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

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
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

    public function getRegisteredAt(): ?DateTime
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(?DateTime $registeredAt): void
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

    public function getResolvedAt(): ?DateTime
    {
        return $this->resolvedAt;
    }

    public function setResolvedAt(?DateTime $resolvedAt): void
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

    public function getClosedAt(): ?DateTime
    {
        return $this->closedAt;
    }

    public function setClosedAt(?DateTime $closedAt): void
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
