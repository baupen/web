<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Exceptions;

use App\Entity\ConstructionManager;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UserWithoutPasswordAuthenticationException extends AuthenticationException
{
    /**
     * @var ConstructionManager
     */
    public $user;
    /**
     * @var string
     */
    private $userId;

    public function __construct(ConstructionManager $user, $message = '', $code = 0, ?\Throwable $previous = null)
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
