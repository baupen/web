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
use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\CommunicationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\PersonTrait;
use App\Entity\Traits\UserTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Migrations\Configuration\ArrayConfiguration;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AppUserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AppUser extends BaseEntity
{
    use IdTrait;
    use PersonTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $identifier;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $passwordHash;


    /**
     * @var string
     */
    private $plainPassword;

    /**
     * @var Building[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Building", inversedBy="appUsers")
     * @ORM\JoinTable(name="app_user_buildings")
     */
    private $buildings;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->buildings = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return Building[]|ArrayCollection
     */
    public function getBuildings()
    {
        return $this->buildings;
    }

    /**
     * hashes the plain password
     */
    public function setPassword()
    {
        $this->passwordHash = hash("sha256", $this->plainPassword);
    }
}
