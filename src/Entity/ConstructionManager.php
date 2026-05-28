<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Api\Processor\ConstructionSiteProcessor;
use App\Entity\Base\BaseEntity;
use App\Entity\Traits\AuthenticationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use App\Entity\Traits\UserTrait;
use App\Enum\Role;
use App\Repository\ConstructionManagerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={
 *      "get",
 *      "post" = {"denormalization_context"={"groups"={"construction-manager:create", "construction-manager:write"}}},
 *     },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('CONSTRUCTION_MANAGER_VIEW', object)"},
 *      "patch" = {"security" = "is_granted('CONSTRUCTION_MANAGER_SELF', object)"}
 *     },
 *     normalizationContext={"groups"={"construction-manager:read"}, "skip_null_values"=false},
 *     denormalizationContext={"groups"={"construction-manager:write"}},
 *     attributes={"pagination_enabled"=false}
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"constructionSites.id": "exact"})
 * @ApiFilter(DateFilter::class, properties={"lastChangedAt"})
 */
#[ORM\Entity(repositoryClass: ConstructionManagerRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    denormalizationContext: ['groups' => ['construction-manager:write', 'user:write']],
    normalizationContext: ['groups' => ['construction-site:read', 'time:read', 'user:read']],
)]
class ConstructionManager extends BaseEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    use IdTrait;
    use TimeTrait;
    use AuthenticationTrait;
    use UserTrait;

    #[Groups(['construction-manager:read', 'construction-manager:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $givenName = null;

    #[Groups(['construction-manager:read', 'construction-manager:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $familyName = null;

    #[Groups(['construction-manager:read', 'construction-manager:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $phone = null;

    /**
     * @var Collection<int, ConstructionSite>
     */
    #[ORM\ManyToMany(targetEntity: ConstructionSite::class, mappedBy: 'constructionManagers')]
    private Collection $constructionSites;

    #[ORM\Column(type: Types::TEXT, options: ['default' => 'de'])]
    private string $locale = 'de';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $authorizationAuthority = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isAdminAccount = false;

    #[Groups(['construction-manager:read-self'])]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $canAssociateSelf = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
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
        return trim($this->getGivenName() . ' ' . $this->getFamilyName());
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
            return [Role::ADMIN];
        }

        if (!$this->getCanAssociateSelf()) {
            return [Role::ASSOCIATED_CONSTRUCTION_MANAGER];
        }

        return [Role::CONSTRUCTION_MANAGER];
    }

    #[Groups(['construction-manager:read'])]
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
