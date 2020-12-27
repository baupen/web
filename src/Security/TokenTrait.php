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

use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Entity\Filter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait TokenTrait
{
    protected function tryGetConstructionManager(?TokenInterface $token): ?ConstructionManager
    {
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();
        if ($user instanceof ConstructionManager) {
            return $user;
        }

        if ($user instanceof AuthenticationToken) {
            return $user->getConstructionManager();
        }

        return null;
    }

    protected function tryGetCraftsman(?TokenInterface $token): ?Craftsman
    {
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();
        if ($user instanceof AuthenticationToken) {
            return $user->getCraftsman();
        }

        return null;
    }

    protected function tryGetFilter(?TokenInterface $token): ?Filter
    {
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();
        if ($user instanceof AuthenticationToken) {
            return $user->getFilter();
        }

        return null;
    }
}
