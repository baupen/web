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
use App\Entity\Craftsman;
use App\Entity\Filter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait TokenTrait
{
    protected function tryGetConstructionManager(?TokenInterface $token): ?ConstructionManager
    {
        if (!$token instanceof TokenInterface) {
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
        if (!$token instanceof TokenInterface) {
            return null;
        }

        $user = $token->getUser();
        if ($user instanceof AuthenticationToken) {
            return $user->getCraftsman();
        }

        return null;
    }

    protected function tryGetAuthority(?TokenInterface $token): ?string
    {
        $constructionManager = $this->tryGetConstructionManager($token);
        if ($constructionManager) {
            return $constructionManager->getId();
        }

        $craftsman = $this->tryGetCraftsman($token);
        if ($craftsman) {
            return $craftsman->getId();
        }

        return null;
    }

    protected function tryGetFilter(?TokenInterface $token): ?Filter
    {
        if (!$token instanceof TokenInterface) {
            return null;
        }

        $user = $token->getUser();
        if ($user instanceof AuthenticationToken) {
            return $user->getFilter();
        }

        return null;
    }
}
