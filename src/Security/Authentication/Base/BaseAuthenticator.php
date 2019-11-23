<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Authentication\Base;

use App\Entity\Traits\UserTrait;

abstract class BaseAuthenticator
{
    /**
     * which source of authentication the authenticator supports.
     *
     * @return string
     */
    abstract public function getAuthenticationSource();

    /**
     * process the user, setting $authenticationStatus and $isEnabled as appropriate.
     */
    public function authenticate(UserTrait $trait)
    {
    }
}
