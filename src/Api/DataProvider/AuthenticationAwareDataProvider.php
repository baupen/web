<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Api\Filters\IsDeletedFilter;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\Map;
use App\Security\TokenTrait;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthenticationAwareDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    use TokenTrait;

    /**
     * @var ContextAwareCollectionDataProviderInterface
     */
    private $decoratedCollectionDataProvider;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    private const ALREADY_CALLED = 'AUTHENTICATION_AWARE_DATA_PROVIDER_ALREADY_CALLED';

    /**
     * IssueReportDataProvider constructor.
     */
    public function __construct(ContextAwareCollectionDataProviderInterface $decoratedCollectionDataProvider, TokenStorageInterface $tokenStorage)
    {
        $this->decoratedCollectionDataProvider = $decoratedCollectionDataProvider;
        $this->tokenStorage = $tokenStorage;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return true;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $token = $this->tokenStorage->getToken();

        $existingFilter = isset($context['filters']) ? $context['filters'] : [];
        if (($constructionManager = $this->tryGetConstructionManager($token))) {
            $this->ensureConstructionManagerQueryValid($constructionManager, $resourceClass, $existingFilter);
        } elseif ($craftsman = $this->tryGetCraftsman($token)) {
            $this->ensureCraftsmanQueryValid($craftsman, $resourceClass, $existingFilter);
        } elseif ($filter = $this->tryGetFilter($token)) {
            $this->ensureFilterQueryValid($filter, $resourceClass, $existingFilter);
        } else {
            $this->ensureRenderQuery($resourceClass, $operationName, $existingFilter);
        }

        return $this->decoratedCollectionDataProvider->getCollection($resourceClass, $operationName, $context);
    }

    private function ensureConstructionManagerQueryValid(ConstructionManager $manager, string $resourceClass, array $query): void
    {
        if ($manager->getCanAssociateSelf() && (ConstructionSite::class === $resourceClass || ConstructionManager::class === $resourceClass)) {
            return;
        }

        $ownConstructionSiteIds = [];
        foreach ($manager->getConstructionSites() as $constructionSite) {
            $ownConstructionSiteIds[] = $constructionSite->getId();
        }

        if (!$manager->getCanAssociateSelf()) {
            if (ConstructionSite::class === $resourceClass) {
                $this->ensureSearchFilterValid($query, 'constructionManagers.id', $manager->getId());

                return;
            }

            if (ConstructionManager::class === $resourceClass) {
                if ($manager->getCanAssociateSelf()) {
                    return;
                }

                $this->ensureSearchFilterValid($query, 'constructionSites.id', $ownConstructionSiteIds);

                return;
            }
        }

        $this->ensureSearchFilterValid($query, 'constructionSite', $ownConstructionSiteIds);
    }

    private function ensureCraftsmanQueryValid(Craftsman $craftsman, string $resourceClass, array $query): void
    {
        if (ConstructionManager::class === $resourceClass) {
            $this->ensureSearchFilterValid($query, 'constructionSites.id', $craftsman->getConstructionSite()->getId());

            return;
        }

        $this->ensureSearchFilterValid($query, 'constructionSite', $craftsman->getConstructionSite()->getId());

        if (Map::class === $resourceClass) {
            return;
        }

        if (Issue::class === $resourceClass) {
            $this->ensureSearchFilterValid($query, 'craftsman', [$craftsman->getId()]);
            $this->ensureDeletedFilterValid($query, 'isDeleted', false);

            return;
        }

        throw new BadRequestException('You are not allowed to query this resource');
    }

    private function ensureFilterQueryValid(Filter $filter, string $resourceClass, array $query): void
    {
        if (ConstructionManager::class === $resourceClass) {
            $this->ensureSearchFilterValid($query, 'constructionSites.id', $filter->getConstructionSite()->getId());

            return;
        }

        $this->ensureSearchFilterValid($query, 'constructionSite', $filter->getConstructionSite()->getId());

        if (Map::class === $resourceClass) {
            $this->ensureSearchFilterValid($query, 'id', $filter->getMapIds());

            return;
        }

        if (Craftsman::class === $resourceClass) {
            $this->ensureSearchFilterValid($query, 'id', $filter->getCraftsmanIds());

            return;
        }

        if (Issue::class === $resourceClass) {
            $this->ensureSearchFilterValid($query, 'isMarked', $filter->getIsMarked());
            $this->ensureSearchFilterValid($query, 'wasAddedWithClient', $filter->getWasAddedWithClient());

            $this->ensureSearchFilterValid($query, 'number', $filter->getNumbers());
            $this->ensureSearchFilterValid($query, 'description', $filter->getDescription());

            $this->ensureSearchFilterValid($query, 'state', $filter->getState());
            $this->ensureSearchFilterValid($query, 'craftsman', $filter->getCraftsmanIds());
            $this->ensureSearchFilterValid($query, 'map', $filter->getMapIds());

            $this->ensureSearchFilterValid($query, 'deadline[before]', $filter->getDeadlineBefore());
            $this->ensureSearchFilterValid($query, 'deadline[after]', $filter->getDeadlineAfter());

            $this->ensureSearchFilterValid($query, 'createdAt[before]', $filter->getCreatedAtBefore());
            $this->ensureSearchFilterValid($query, 'createdAt[after]', $filter->getCreatedAtAfter());
            $this->ensureSearchFilterValid($query, 'registeredAt[before]', $filter->getRegisteredAtBefore());
            $this->ensureSearchFilterValid($query, 'registeredAt[after]', $filter->getRegisteredAtAfter());
            $this->ensureSearchFilterValid($query, 'resolvedAt[before]', $filter->getResolvedAtBefore());
            $this->ensureSearchFilterValid($query, 'resolvedAt[after]', $filter->getResolvedAtAfter());
            $this->ensureSearchFilterValid($query, 'closedAt[before]', $filter->getClosedAtBefore());
            $this->ensureSearchFilterValid($query, 'closedAt[after]', $filter->getClosedAtAfter());

            return;
        }

        throw new BadRequestException('You are not allowed to query this resource');
    }

    private function ensureSearchFilterValid(array $query, string $property, $restriction): void
    {
        if (null === $restriction) {
            return;
        }

        if (is_array($restriction)) {
            $filterValid = is_array($query[$property]) ?
                empty(array_diff($restriction, $query[$property])) :
                in_array($query[$property], $restriction);

            if ($filterValid) {
                return;
            }

            throw new BadRequestException($property.' filter missing or value no one of '.implode(', ', $restriction).'.');
        }

        if (isset($query[$property]) && $query[$property] == $restriction) {
            return;
        }

        throw new BadRequestException($property.' filter missing or value not equal to '.$restriction.'.');
    }

    private function ensureDeletedFilterValid(array $query, string $property, ?bool $expectedValue): void
    {
        if (null === $expectedValue) {
            return;
        }

        if (isset($query[$property]) && IsDeletedFilter::normalizeValue($query[$property]) === $expectedValue) {
            return;
        }

        throw new BadRequestException($property.' filter missing or value not equal to '.$expectedValue.'.');
    }

    private function ensureRenderQuery(string $resourceClass, ?string $operationName, array $existingFilter)
    {
        if (Issue::class === $resourceClass && 'get_render' === $operationName) {
            if (isset($existingFilter['map']) && !isset($existingFilter['map[]'])) {
                return;
            }
        }

        throw new HttpException(Response::HTTP_FORBIDDEN);
    }
}
