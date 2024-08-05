<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        if ($user instanceof ConstructionManager) {
            if (!$user->getIsEnabled()) {
                throw new DisabledException();
            }
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if ($user instanceof ConstructionManager) {
            if (null === $user->getPassword()) {
                throw new UserWithoutPasswordAuthenticationException($user);
            }
        }
    }
}
