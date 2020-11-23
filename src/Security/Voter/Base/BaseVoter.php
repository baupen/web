<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter\Base;

use App\Entity\AuthenticationToken;
use App\Entity\ConstructionManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class BaseVoter extends Voter
{
    protected function tryGetConstructionManager(TokenInterface $token): ?ConstructionManager
    {
        $user = $token->getUser();
        if ($user instanceof ConstructionManager) {
            return $user;
        }

        if ($user instanceof AuthenticationToken) {
            return $user->getConstructionManager();
        }

        return null;
    }
}
