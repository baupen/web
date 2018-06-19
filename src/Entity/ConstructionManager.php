<?php

/*
 * This file is part of the nodika project.
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
 * @ORM\Entity
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
    private $name;

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
     * @var Issue[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Issue", mappedBy="registrationBy")
     */
    private $markers;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->constructionSites = new ArrayCollection();
        $this->markers = new ArrayCollection();
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param null|string $phone
     */
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
     * @return Issue[]|ArrayCollection
     */
    public function getMarkers()
    {
        return $this->markers;
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
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return ["ROLE_USER"];
    }
}
