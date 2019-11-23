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

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use App\Entity\Traits\UserTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="construction_manager")
 * @ORM\Entity(repositoryClass="App\Repository\ConstructionManagerRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ConstructionManager extends BaseEntity implements UserInterface
{
    use IdTrait;
    use TimeTrait;
    use UserTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $givenName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $familyName;

    /**
     * @var string|null
     *
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
     * @var ConstructionSite|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ConstructionSite")
     */
    private $activeConstructionSite;

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
    private $isTrialAccount = false;

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
        return $this->getGivenName() . ' ' . $this->getFamilyName();
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
        if ($this->getIsTrialAccount()) {
            return ['ROLE_USER', 'ROLE_TRIAL_ACCOUNT'];
        }

        return ['ROLE_USER'];
    }

    public function getActiveConstructionSite(): ?ConstructionSite
    {
        return $this->activeConstructionSite;
    }

    public function setActiveConstructionSite(?ConstructionSite $activeConstructionSite): void
    {
        $this->activeConstructionSite = $activeConstructionSite;
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
}
