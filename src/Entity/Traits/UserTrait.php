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
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

trait UserTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $password;

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
    private $authenticationHash;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isEnabled = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isRegistrationCompleted = false;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $registrationDate;

    /**
     * @var string
     */
    private $plainPassword;
    private $repeatPlainPassword;

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
     * @return DateTime
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * @throws Exception
     */
    public function setRegistrationDate()
    {
        $this->registrationDate = new DateTime();
    }

    /**
     * @return string
     */
    public function getAuthenticationHash()
    {
        return $this->authenticationHash;
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
     *
     * @return static
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getRepeatPlainPassword()
    {
        return $this->repeatPlainPassword;
    }

    /**
     * @param string $plainPassword
     *
     * @return static
     */
    public function setRepeatPlainPassword($plainPassword)
    {
        $this->repeatPlainPassword = $plainPassword;

        return $this;
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
     * sha256 hash of the password.
     *
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
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
        $this->setPlainPassword(null);
        $this->setRepeatPlainPassword(null);
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
     *
     * @param bool $preventErase
     */
    public function setPassword(bool $preventErase = false)
    {
        $this->password = password_hash($this->getPlainPassword(), PASSWORD_BCRYPT);
        $this->passwordHash = hash('sha256', $this->getPlainPassword());

        if (!$preventErase) {
            $this->eraseCredentials();
        }
    }

    /**
     * creates a new reset hash.
     */
    public function setAuthenticationHash()
    {
        $this->authenticationHash = HashHelper::getHash();
    }

    /**
     * indicates that the user registered successfully.
     */
    public function setRegistrationCompleted()
    {
        $this->isRegistrationCompleted = true;
    }

    /**
     * checks if the user has completed the registration.
     */
    public function isRegistrationCompleted()
    {
        return $this->isRegistrationCompleted;
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
