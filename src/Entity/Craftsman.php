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

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filters\IsDeletedFilter;
use App\Api\Filters\RequiredExactSearchFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Traits\AuthenticationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Entity\Traits\TimeTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * a craftsman receives information about open issues, and answers them.
 *
 * @ApiResource(
 *     collectionOperations={
 *      "get",
 *      "post" = {"security_post_denormalize" = "is_granted('CRAFTSMAN_MODIFY', object)", "denormalization_context"={"groups"={"craftsman-create", "craftsman-write"}}},
 *      "get_feed_entries"={
 *          "method"="GET",
 *          "path"="/craftsmen/feed_entries"
 *      },
 *      "get_statistics"={
 *          "method"="GET",
 *          "path"="/craftsmen/statistics"
 *      }
 *      },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('CRAFTSMAN_VIEW', object)"},
 *      "patch" = {"security" = "is_granted('CRAFTSMAN_MODIFY', object)"},
 *      "delete" = {"security" = "is_granted('CRAFTSMAN_MODIFY', object)"},
 *     },
 *     normalizationContext={"groups"={"craftsman-read"}, "skip_null_values"=false},
 *     denormalizationContext={"groups"={"craftsman-write"}},
 *     attributes={"pagination_enabled"=false}
 * )
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "trade": "exact"})
 * @ApiFilter(RequiredExactSearchFilter::class, properties={"constructionSite"})
 * @ApiFilter(IsDeletedFilter::class, properties={"isDeleted"})
 * @ApiFilter(DateFilter::class, properties={"lastChangedAt"})
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Craftsman extends BaseEntity implements ConstructionSiteOwnedEntityInterface
{
    use IdTrait;
    use TimeTrait;
    use AuthenticationTrait;
    use SoftDeleteTrait;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Groups({"craftsman-read", "craftsman-write"})
     * @ORM\Column(type="text")
     */
    private $contactName;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Groups({"craftsman-read", "craftsman-write"})
     * @ORM\Column(type="text")
     */
    private $company;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Groups({"craftsman-read", "craftsman-write"})
     * @ORM\Column(type="text")
     */
    private $trade;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Groups({"craftsman-read", "craftsman-write"})
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @var string[]
     *
     * @Groups({"craftsman-read", "craftsman-write"})
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $emailCCs;

    /**
     * @var ConstructionSite
     *
     * @Assert\NotBlank
     * @Groups({"craftsman-create"})
     * @ORM\ManyToOne(targetEntity="ConstructionSite", inversedBy="craftsmen")
     */
    private $constructionSite;

    /**
     * @var Issue[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Issue", mappedBy="craftsman")
     */
    private $issues;

    /**
     * @var Issue[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Issue", mappedBy="resolvedBy")
     */
    private $resolvedIssues;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastEmailReceived;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastVisitOnline;

    /**
     * Craftsman constructor.
     */
    public function __construct()
    {
        $this->issues = new ArrayCollection();
        $this->resolvedIssues = new ArrayCollection();
    }

    public function getContactName(): string
    {
        return $this->contactName;
    }

    public function setContactName(string $contactName): void
    {
        $this->contactName = $contactName;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    public function getTrade(): string
    {
        return $this->trade;
    }

    public function setTrade(string $trade): void
    {
        $this->trade = $trade;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string[]
     */
    public function getEmailCCs(): array
    {
        return $this->emailCCs;
    }

    /**
     * @param string[] $emailCCs
     */
    public function setEmailCCs(array $emailCCs): void
    {
        $this->emailCCs = $emailCCs;
    }

    public function isConstructionSiteSet(): bool
    {
        return null !== $this->constructionSite;
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    /**
     * @return Issue[]|ArrayCollection
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * @return Issue[]|ArrayCollection
     */
    public function getResolvedIssues()
    {
        return $this->resolvedIssues;
    }

    public function getName(): string
    {
        return $this->getTrade().' ('.$this->getCompany().')';
    }

    public function getLastEmailReceived(): ?DateTime
    {
        return $this->lastEmailReceived;
    }

    public function setLastEmailReceived(?DateTime $lastEmailReceived): void
    {
        $this->lastEmailReceived = $lastEmailReceived;
    }

    public function getLastVisitOnline(): ?DateTime
    {
        return $this->lastVisitOnline;
    }

    public function setLastVisitOnline(?DateTime $lastVisitOnline): void
    {
        $this->lastVisitOnline = $lastVisitOnline;
    }

    /**
     * @return DateTime|null
     */
    public function getLastAction()
    {
        $lastAction = $this->getLastVisitOnline();
        if (null === $lastAction || $lastAction < $this->getLastEmailReceived()) {
            return $this->getLastEmailReceived();
        }

        return $lastAction;
    }

    /**
     * @Groups({"craftsman-read"})
     */
    public function getIsDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    /**
     * @Groups({"craftsman-read"})
     */
    public function getLastChangedAt(): DateTime
    {
        return $this->lastChangedAt;
    }
}
