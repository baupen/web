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
use App\Api\Filters\PatchedExactSearchFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Traits\AuthenticationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use App\Entity\Traits\UserTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={
 *      "get",
 *      "post" = {"denormalization_context"={"groups"={"construction-manager-create", "construction-manager-write"}}},
 *     },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('CONSTRUCTION_MANAGER_VIEW', object)"},
 *      "patch" = {"security" = "is_granted('CONSTRUCTION_MANAGER_SELF', object)"}
 *     },
 *     normalizationContext={"groups"={"construction-manager-read"}, "skip_null_values"=false},
 *     denormalizationContext={"groups"={"construction-manager-write"}},
 *     attributes={"pagination_enabled"=false}
 * )
 *
 * @ApiFilter(PatchedExactSearchFilter::class, properties={"constructionSites.id": "exact"})
 * @ApiFilter(DateFilter::class, properties={"lastChangedAt"})
 */
#[ORM\Entity(repositoryClass: \App\Repository\ConstructionManagerRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ConstructionManager extends BaseEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    use IdTrait;
    use TimeTrait;
    use AuthenticationTrait;
    use UserTrait;

    public const AUTHORIZATION_AUTHORITY_WHITELIST = 'AUTHORIZATION_AUTHORITY_WHITELIST';

    // can use any features & impersonate users
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    // can use any features
    public const ROLE_CONSTRUCTION_MANAGER = 'ROLE_CONSTRUCTION_MANAGER';

    // can not see data related to other construction sites (including the other construction sites itself)
    public const ROLE_ASSOCIATED_CONSTRUCTION_MANAGER = 'ROLE_ASSOCIATED_CONSTRUCTION_MANAGER';

    #[Groups(['construction-manager-read', 'construction-manager-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $givenName = null;

    #[Groups(['construction-manager-read', 'construction-manager-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $familyName = null;

    #[Groups(['construction-manager-read', 'construction-manager-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $phone = null;

    /**
     * @var Collection<int, ConstructionSite>
     */
    #[ORM\ManyToMany(targetEntity: \ConstructionSite::class, mappedBy: 'constructionManagers')]
    private Collection $constructionSites;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, options: ['default' => 'de'])]
    private string $locale = 'de';

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $authorizationAuthority = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN, options: ['default' => false])]
    private bool $isAdminAccount = false;

    #[Groups(['construction-manager-read-self'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN, options: ['default' => false])]
    private bool $canAssociateSelf = false;

    #[Groups(['construction-manager-read-self', 'construction-manager-write'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN, options: ['default' => false])]
    private bool $receiveWeekly = false;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->constructionSites = new ArrayCollection();
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): void
    {
        $this->givenName = $givenName;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): void
    {
        $this->familyName = $familyName;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return Collection<int, ConstructionSite>
     */
    public function getConstructionSites(): Collection
    {
        return $this->constructionSites;
    }

    public function getName(): string
    {
        return trim($this->getGivenName().' '.$this->getFamilyName());
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return string[] The user roles
     */
    public function getRoles(): array
    {
        if ($this->isAdminAccount || str_ends_with($this->email, '@baupen.ch')) {
            return [self::ROLE_ADMIN];
        }

        if (!$this->getCanAssociateSelf()) {
            return [self::ROLE_ASSOCIATED_CONSTRUCTION_MANAGER];
        }

        return [self::ROLE_CONSTRUCTION_MANAGER];
    }

    #[Groups(['construction-manager-read'])]
    public function getLastChangedAt(): \DateTimeInterface
    {
        return $this->lastChangedAt;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getAuthorizationAuthority(): ?string
    {
        return $this->authorizationAuthority;
    }

    public function setAuthorizationAuthority(?string $authorizationAuthority): void
    {
        $this->authorizationAuthority = $authorizationAuthority;
    }

    public function getCanAssociateSelf(): bool
    {
        return $this->canAssociateSelf;
    }

    public function setCanAssociateSelf(bool $canAssociateSelf): void
    {
        $this->canAssociateSelf = $canAssociateSelf;
    }

    public function getIsAdminAccount(): bool
    {
        return $this->isAdminAccount;
    }

    public function getReceiveWeekly(): bool
    {
        return $this->receiveWeekly;
    }

    public function setReceiveWeekly(bool $receiveWeekly): void
    {
        $this->receiveWeekly = $receiveWeekly;
    }
}
