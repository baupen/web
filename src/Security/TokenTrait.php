<?php

namespace App\Security;

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Filter;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
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

    protected function getConstructionSiteRestriction(TokenInterface $token): ?array
    {
        if (($constructionManager = $this->tryGetConstructionManager($token))) {
            $ownConstructionSiteIds = array_map(static fn(ConstructionSite $constructionSite) => $constructionSite->getId(), $constructionManager->getConstructionSites()->toArray());
            $constructionSiteRestriction = $constructionManager->getCanAssociateSelf() ? null : $ownConstructionSiteIds;
        } elseif (($craftsman = $this->tryGetCraftsman($token))) {
            $constructionSiteRestriction = [$craftsman->getConstructionSite()->getId()];
        } elseif (($filter = $this->tryGetFilter($token))) {
            $constructionSiteRestriction = [$filter->getConstructionSite()->getId()];
        } else {
            throw new BadRequestException('Invalid authentication');
        }

        return $constructionSiteRestriction;
    }
}
