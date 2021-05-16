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
use Throwable;

class UserWithoutPasswordAuthenticationException extends AuthenticationException
{
    /**
     * @var ConstructionManager
     */
    private $user;

    public function __construct(ConstructionManager $user, $message = '', $code = 0, Throwable $previous = null)
    {
        $this->user = $user;
        parent::__construct($message, $code, $previous);
    }

    public function getUser(): ConstructionManager
    {
        return $this->user;
    }
}
