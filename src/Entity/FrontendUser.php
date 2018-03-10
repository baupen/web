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
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\UserTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Migrations\Configuration\ArrayConfiguration;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FrontendUserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class FrontendUser extends BaseEntity implements AdvancedUserInterface, EquatableInterface
{
    use IdTrait;
    use UserTrait;
    use AddressTrait;

    /**
     * @var Setting[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Setting", mappedBy="frontendUser")
     */
    private $settings;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->settings = new ArrayCollection();
    }

    /**
     * Returns the roles granted to the user.
     *
     * @return array (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return ['ROLE_FRONTEND_USER'];
    }

    /**
     * check if this is the same user
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!($user instanceof static)) {
            return false;
        }

        return $this->isEqualToUser($user);
    }

    /**
     * returns a string representation of this entity.
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getUserIdentifier();
    }

    /**
     * @return Setting[]|ArrayCollection
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
