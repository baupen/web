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
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use App\Entity\Traits\UserTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"construction-manager-read"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ConstructionManagerRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ConstructionManager extends BaseEntity implements UserInterface
{
    use IdTrait;
    use TimeTrait;
    use UserTrait;

    // can use any features & impersonate users
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    // can use any features
    public const ROLE_CONSTRUCTION_MANAGER = 'ROLE_CONSTRUCTION_MANAGER';

    // can not see other construction sites
    public const ROLE_ASSIGNED_CONSTRUCTION_MANAGER = 'ROLE_ASSIGNED_CONSTRUCTION_MANAGER';

    /**
     * @var string|null
     *
     * @Groups({"construction-manager-read"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $givenName;

    /**
     * @var string|null
     *
     * @Groups({"construction-manager-read"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $familyName;

    /**
     * @var string|null
     *
     * @Groups({"construction-manager-read"})
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
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isAdminAccount = false;

    /**
     * @var bool
     *           added itself using a trial account offering like in the app to a specific construction site
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isTrialAccount = false;

    /**
     * @var bool
     *           added by other construction managers to specific construction sites
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isExternalAccount = false;

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

        if (!$this->getIsTrialAccount() && !$this->getIsExternalAccount()) {
            return [self::ROLE_CONSTRUCTION_MANAGER];
        }

        return [self::ROLE_ASSIGNED_CONSTRUCTION_MANAGER];
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getIsTrialAccount(): bool
    {
        return $this->isTrialAccount;
    }

    public function setIsTrialAccount(bool $isTrialAccount): void
    {
        $this->isTrialAccount = $isTrialAccount;
    }

    public function getIsExternalAccount(): bool
    {
        return $this->isExternalAccount;
    }

    public function setIsExternalAccount(bool $isExternalAccount): void
    {
        $this->isExternalAccount = $isExternalAccount;
    }

    public function __toString()
    {
        return 'hi';
    }
}
