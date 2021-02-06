<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Traits\AuthenticationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Filter is used to share a selection of issues.
 *
 * @ApiResource(
 *     collectionOperations={
 *      "post" = {"security_post_denormalize" = "is_granted('FILTER_CREATE', object)", "denormalization_context"={"groups"={"filter-create"}}}
 *      },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('FILTER_VIEW', object)"}
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\IssueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Filter extends BaseEntity implements ConstructionSiteOwnedEntityInterface
{
    use IdTrait;
    use AuthenticationTrait;
    use TimeTrait;

    /**
     * @var bool|null
     *
     * @Groups({"filter-read", "filter-create"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isMarked;

    /**
     * @var int|null
     *
     * @Groups({"filter-read", "filter-create"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $state;

    /**
     * @var DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registeredAtAfter;

    /**
     * @var DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registeredAtBefore;

    /**
     * @var DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resolvedAtAfter;

    /**
     * @var DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resolvedAtBefore;

    /**
     * @var DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closedAtAfter;

    /**
     * @var DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closedAtBefore;

    /**
     * @var DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deadlineAtBefore;

    /**
     * @var DateTime|null
     *
     * @Groups({"filter-read", "filter-create"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deadlineAtAfter;

    /**
     * @var string[]|null
     *
     * @Groups({"filter-read", "filter-create"})
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $craftsmanIds;

    /**
     * @var string[]|null
     *
     * @Groups({"filter-read", "filter-create"})
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $craftsmanTrades;

    /**
     * @var string[]|null
     *
     * @Groups({"filter-read", "filter-create"})
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $mapIds;

    /**
     * @var DateTime|null
     *
     * @Groups({"filter-create"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $accessAllowedBefore;

    /**
     * @var ConstructionSite
     *
     * @Groups({"filter-read", "filter-create"})
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="ConstructionSite", inversedBy="filters")
     */
    private $constructionSite;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastUsedAt;

    /**
     * @var Issue[]
     *
     * @Groups({"filter-read"})
     */
    private $issues = [];

    public function getIsMarked(): ?bool
    {
        return $this->isMarked;
    }

    public function setIsMarked(?bool $isMarked): void
    {
        $this->isMarked = $isMarked;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(?int $state): void
    {
        $this->state = $state;
    }

    public function getRegisteredAtAfter(): ?DateTime
    {
        return $this->registeredAtAfter;
    }

    public function setRegisteredAtAfter(?DateTime $registeredAtAfter): void
    {
        $this->registeredAtAfter = $registeredAtAfter;
    }

    public function getRegisteredAtBefore(): ?DateTime
    {
        return $this->registeredAtBefore;
    }

    public function setRegisteredAtBefore(?DateTime $registeredAtBefore): void
    {
        $this->registeredAtBefore = $registeredAtBefore;
    }

    public function getResolvedAtAfter(): ?DateTime
    {
        return $this->resolvedAtAfter;
    }

    public function setResolvedAtAfter(?DateTime $resolvedAtAfter): void
    {
        $this->resolvedAtAfter = $resolvedAtAfter;
    }

    public function getResolvedAtBefore(): ?DateTime
    {
        return $this->resolvedAtBefore;
    }

    public function setResolvedAtBefore(?DateTime $resolvedAtBefore): void
    {
        $this->resolvedAtBefore = $resolvedAtBefore;
    }

    public function getClosedAtAfter(): ?DateTime
    {
        return $this->closedAtAfter;
    }

    public function setClosedAtAfter(?DateTime $closedAtAfter): void
    {
        $this->closedAtAfter = $closedAtAfter;
    }

    public function getClosedAtBefore(): ?DateTime
    {
        return $this->closedAtBefore;
    }

    public function setClosedAtBefore(?DateTime $closedAtBefore): void
    {
        $this->closedAtBefore = $closedAtBefore;
    }

    public function getDeadlineAtBefore(): ?DateTime
    {
        return $this->deadlineAtBefore;
    }

    public function setDeadlineAtBefore(?DateTime $deadlineAtBefore): void
    {
        $this->deadlineAtBefore = $deadlineAtBefore;
    }

    public function getDeadlineAtAfter(): ?DateTime
    {
        return $this->deadlineAtAfter;
    }

    public function setDeadlineAtAfter(?DateTime $deadlineAtAfter): void
    {
        $this->deadlineAtAfter = $deadlineAtAfter;
    }

    /**
     * @return string[]|null
     */
    public function getCraftsmanIds(): ?array
    {
        if (empty($this->craftsmanIds)) {
            return null;
        }

        return $this->craftsmanIds;
    }

    /**
     * @param string[]|null $craftsmanIds
     */
    public function setCraftsmanIds(?array $craftsmanIds): void
    {
        $this->craftsmanIds = $craftsmanIds;
    }

    /**
     * @return string[]|null
     */
    public function getCraftsmanTrades(): ?array
    {
        if (empty($this->craftsmanTrades)) {
            return null;
        }

        return $this->craftsmanTrades;
    }

    /**
     * @param string[]|null $craftsmanTrades
     */
    public function setCraftsmanTrades(?array $craftsmanTrades): void
    {
        $this->craftsmanTrades = $craftsmanTrades;
    }

    /**
     * @return string[]|null
     */
    public function getMapIds(): ?array
    {
        if (empty($this->mapIds)) {
            return null;
        }

        return $this->mapIds;
    }

    /**
     * @param string[]|null $mapIds
     */
    public function setMapIds(?array $mapIds): void
    {
        $this->mapIds = $mapIds;
    }

    public function getAccessAllowedBefore(): ?DateTime
    {
        return $this->accessAllowedBefore;
    }

    public function setAccessAllowedBefore(?DateTime $accessAllowedBefore): void
    {
        $this->accessAllowedBefore = $accessAllowedBefore;
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    public function isConstructionSiteSet(): bool
    {
        return null !== $this->constructionSite;
    }

    /**
     * @return Issue[]
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    /**
     * @param Issue[] $issues
     */
    public function setIssues(array $issues): void
    {
        $this->issues = $issues;
    }

    public function getLastUsedAt(): ?DateTime
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(?DateTime $lastUsedAt): void
    {
        $this->lastUsedAt = $lastUsedAt;
    }
}
