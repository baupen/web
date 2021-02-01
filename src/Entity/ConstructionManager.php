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
use App\Api\Filters\ExactSearchFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Traits\AuthenticationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use App\Entity\Traits\UserTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={"get", "post"},
 *     itemOperations={"get" = {"security" = "is_granted('CONSTRUCTION_MANAGER_VIEW', object)"}},
 *     normalizationContext={"groups"={"construction-manager-read"}, "skip_null_values"=false},
 *     denormalizationContext={"groups"={"construction-manager-write"}},
 *     attributes={"pagination_enabled"=false}
 * )
 * @ApiFilter(ExactSearchFilter::class, properties={"constructionSites.id": "exact"})
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class ConstructionManager extends BaseEntity implements UserInterface
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

    /**
     * @var string|null
     *
     * @Groups({"construction-manager-read", "construction-manager-write"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $givenName;

    /**
     * @var string|null
     *
     * @Groups({"construction-manager-read", "construction-manager-write"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $familyName;

    /**
     * @var string|null
     *
     * @Groups({"construction-manager-read", "construction-manager-write"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $phone;

    /**
     * @var ConstructionSite[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ConstructionSite", mappedBy="constructionManagers")
     */
    private $constructionSites;

    /**
     * @var string
     *
     * @ORM\Column(type="text", options={"default": "de"})
     */
    private $locale = 'de';

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $authorizationAuthority;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isAdminAccount = false;

    /**
     * @var bool
     *
     * @Groups({"construction-manager-read-self"})
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $canAssociateSelf = false;

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
     * @return ConstructionSite[]|ArrayCollection
     */
    public function getConstructionSites()
    {
        return $this->constructionSites;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getGivenName().' '.$this->getFamilyName();
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
    public function getRoles()
    {
        if ($this->isAdminAccount) {
            return [self::ROLE_ADMIN];
        }

        if (!$this->getCanAssociateSelf()) {
            return [self::ROLE_ASSOCIATED_CONSTRUCTION_MANAGER];
        }

        return [self::ROLE_CONSTRUCTION_MANAGER];
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

    public function setIsExternalAccount(bool $isExternalAccount): void
    {
        $this->isExternalAccount = $isExternalAccount;
    }
}
