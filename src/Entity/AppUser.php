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


use App\Api\ApiSerializable;
use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\PersonTrait;
use App\Entity\Traits\UserTrait;
use App\Enum\MarkerType;
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
class AppUser extends BaseEntity implements ApiSerializable
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
     *
     * @ORM\Column(type="text")
     */
    private $authenticationToken;

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
     * @var Marker[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Marker", mappedBy="createdBy")
     */
    private $markers;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->buildings = new ArrayCollection();
        $this->markers = new ArrayCollection();
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

    /**
     * sets a new authentication token
     */
    public function setAuthenticationToken()
    {
        $newHash = '';
        //0-9, A-Z, a-z
        $allowedRanges = [[48, 57], [65, 90], [97, 122]];
        for ($i = 0; $i < 20; ++$i) {
            $rand = mt_rand(20, 160);
            $allowed = false;
            for ($j = 0; $j < count($allowedRanges); ++$j) {
                if ($allowedRanges[$j][0] <= $rand && $allowedRanges[$j][1] >= $rand) {
                    $allowed = true;
                }
            }
            if ($allowed) {
                $newHash .= chr($rand);
            } else {
                --$i;
            }
        }

        $this->authenticationToken = $newHash;
    }

    /**
     * @return string
     */
    public function getAuthenticationToken()
    {
        return $this->authenticationToken;
    }

    /**
     * @return Marker[]|ArrayCollection
     */
    public function getMarkers()
    {
        return $this->markers;
    }

    /**
     * remove all array collections, setting them to null
     */
    public function flattenDoctrineStructures()
    {
        $this->markers = null;
        $this->buildings = null;
    }

    public function __toString()
    {
        return $this->getFullName();
    }
}
