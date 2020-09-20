<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use App\Helper\HashHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait UserTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $password;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $authenticationHash;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isEnabled = false;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return static
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param bool $isEnabled
     *
     * @return static
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * @return string
     */
    public function generateAuthenticationHash()
    {
        $this->authenticationHash = HashHelper::getHash();

        return $this->authenticationHash;
    }

    /**
     * checks if the user is allowed to login.
     *
     * @return bool
     */
    public function canLogin()
    {
        return $this->isEnabled;
    }

    public function getAuthenticationHash(): ?string
    {
        return $this->authenticationHash;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // nothing to do
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * hashes the plainPassword and erases credentials.
     */
    public function setPasswordFromPlain(string $plainPassword)
    {
        $this->password = password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    /**
     * checks if the user has completed the registration.
     */
    public function getRegistrationCompleted()
    {
        return null !== $this->password;
    }

    /**
     * get the user identifier.
     *
     * @return string
     */
    protected function getUserIdentifier()
    {
        return $this->email;
    }

    /**
     * check if two users are equal.
     *
     * @param UserTrait $user
     *
     * @return bool
     */
    protected function isEqualToUser($user)
    {
        /** @var UserTrait $user */
        if ($this->getUsername() !== $user->getUsername()) {
            return false;
        }

        if ($this->getPassword() !== $user->getPassword()) {
            return false;
        }

        return true;
    }
}
