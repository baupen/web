<?php

namespace App\Entity\Traits;

use App\Helper\HashHelper;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait UserTrait
{
    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $authenticationHash = null;

    #[Groups(['user:read'])]
    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isEnabled = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $registrationCompletedAt = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(?bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function setAuthenticationHash(): string
    {
        $this->authenticationHash = HashHelper::getHash();

        return $this->authenticationHash;
    }

    public function getAuthenticationHash(): ?string
    {
        return $this->authenticationHash;
    }

    public function setRegistrationCompletedNow(): void
    {
        $this->registrationCompletedAt = new \DateTimeImmutable();
    }

    public function getRegistrationCompletedAt(): ?\DateTimeImmutable
    {
        return $this->registrationCompletedAt;
    }

    /**
     * checks if the user has completed the registration.
     */
    public function getRegistrationCompleted(): bool
    {
        return null !== $this->password;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return ?string The password
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials(): void
    {
        // nothing to do
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
