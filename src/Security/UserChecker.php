<?php

namespace App\Security;

use App\Entity\ConstructionManager;
use App\Security\Exceptions\UserWithoutPasswordAuthenticationException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof ConstructionManager && !$user->getIsEnabled()) {
            throw new DisabledException();
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if ($user instanceof ConstructionManager && null === $user->getPassword()) {
            throw new UserWithoutPasswordAuthenticationException($user);
        }
    }
}
