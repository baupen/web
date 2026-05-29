<?php

namespace App\Security\Exceptions;

use App\Entity\ConstructionManager;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UserWithoutPasswordAuthenticationException extends AuthenticationException
{
    public ConstructionManager $user;
    private string $userId;

    public function __construct(ConstructionManager $user, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        $this->user = $user;
        $this->userId = $user->getId();
        parent::__construct($message, $code, $previous);
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function __serialize(): array
    {
        return [$this->userId, parent::__serialize()];
    }

    public function __unserialize(array $data): void
    {
        [$this->userId, $parentData] = $data;
        parent::__unserialize($parentData);
    }
}
