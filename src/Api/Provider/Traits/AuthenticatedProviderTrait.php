<?php

namespace App\Api\Provider\Traits;

use App\Entity\ConstructionSite;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait AuthenticatedProviderTrait
{
    private TokenStorageInterface $tokenStorage;

    private function ensureConstructionSiteFiltered(array $context): void
    {
        $token = $this->tokenStorage->getToken();

        $existingFilter = $context['filters'] ?? [];
        if (($constructionManager = $this->tryGetConstructionManager($token))) {
            $ownConstructionSites = array_map(static fn(ConstructionSite $constructionSite) => $constructionSite->getId(), $constructionManager->getConstructionSites()->toArray());
            $constructionSiteRestriction = $constructionManager->getCanAssociateSelf() ? null : $ownConstructionSites;
        } elseif (($craftsman = $this->tryGetCraftsman($token))) {
            $constructionSiteRestriction = [$craftsman->getConstructionSite()->getId()];
        } elseif (($filter = $this->tryGetFilter($token))) {
            $constructionSiteRestriction = [$filter->getConstructionSite()->getId()];
        } else {
            throw new BadRequestException('Invalid authentication');
        }

        $this->ensureArraySearchFilterValid($existingFilter, 'constructionSite', $constructionSiteRestriction);
    }

    private function ensureArraySearchFilterValid(array $query, string $property, ?array $restriction): void
    {
        if (!$restriction) {
            return;
        }

        if (!isset($query[$property])) {
            throw new BadRequestException($property . ' filter missing.');
        }

        if (is_array($query[$property])) {
            if ([] !== array_diff($query[$property], $restriction)) {
                throw new BadRequestException($property . ' filter value ' . implode($query[$property]) . ' not equal ' . implode($restriction) . '.');
            }
        } elseif (!in_array($query[$property], $restriction)) {
            throw new BadRequestException($property . ' filter value ' . $query[$property] . ' not in ' . implode($restriction) . '.');
        }
    }

}
