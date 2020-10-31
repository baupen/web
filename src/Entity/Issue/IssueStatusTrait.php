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
     * @Groups({"issue-read", "issue-write"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registeredAt;

    /**
     * @var ConstructionManager|null
     *
     * @Groups({"issue-read", "issue-write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $registrationBy;

    /**
     * @var DateTime|null
     *
     * @Groups({"issue-read", "issue-write", "issue-craftsman-write"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $respondedAt;

    /**
     * @var Craftsman|null
     *
     * @Groups({"issue-read", "issue-write", "issue-craftsman-write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Craftsman", inversedBy="respondedIssues")
     */
    private $responseBy;

    /**
     * @var DateTime|null
     *
     * @Groups({"issue-read", "issue-write"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $reviewedAt;

    /**
     * @var ConstructionManager|null
     *
     * @Groups({"issue-read", "issue-write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionManager")
     */
    private $reviewBy;

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

    public function getRegistrationBy(): ?ConstructionManager
    {
        return $this->registrationBy;
    }

    public function setRegistrationBy(?ConstructionManager $registrationBy): void
    {
        $this->registrationBy = $registrationBy;
    }

    public function getRespondedAt(): ?DateTime
    {
        return $this->respondedAt;
    }

    public function setRespondedAt(?DateTime $respondedAt): void
    {
        $this->respondedAt = $respondedAt;
    }

    public function getResponseBy(): ?Craftsman
    {
        return $this->responseBy;
    }

    public function setResponseBy(?Craftsman $responseBy): void
    {
        $this->responseBy = $responseBy;
    }

    public function getReviewedAt(): ?DateTime
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(?DateTime $reviewedAt): void
    {
        $this->reviewedAt = $reviewedAt;
    }

    public function getReviewBy(): ?ConstructionManager
    {
        return $this->reviewBy;
    }

    public function setReviewBy(?ConstructionManager $reviewBy): void
    {
        $this->reviewBy = $reviewBy;
    }
}
