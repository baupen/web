<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;

use App\Security\Model\UserToken;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ConstructionManagerChecker implements UserCheckerInterface
{
    const ERROR_ACCOUNT_DISABLED = 'User account is disabled';

    /**
     * Checks the user account before authentication.
     */
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof UserToken) {
            return;
        }

        /** @var UserToken $user */
        if (!$user->getIsEnabled()) {
            $ex = new DisabledException(self::ERROR_ACCOUNT_DISABLED);
            $ex->setUser($user);
            throw $ex;
        }
    }

    /**
     * Checks the user account after authentication.
     */
    public function checkPostAuth(UserInterface $user)
    {
        // does not require any checks
    }
}
