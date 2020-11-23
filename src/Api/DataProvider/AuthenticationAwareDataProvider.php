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
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\Map;
use App\Security\TokenTrait;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthenticationAwareDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
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

    use TokenTrait;

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $token = $this->tokenStorage->getToken();

        $existingFilter = isset($context['filters']) ? $context['filters'] : [];
        $validQuery = false;
        if (($constructionManager = $this->tryGetConstructionManager($token))) {
            $validQuery = $this->isConstructionManagerQueryValid($constructionManager, $resourceClass, $existingFilter);
        } elseif ($craftsman = $this->tryGetCraftsman($token)) {
            $validQuery = $this->isCraftsmanQueryValid($craftsman, $resourceClass, $existingFilter);
        } elseif ($filter = $this->tryGetFilter($token)) {
            $validQuery = $this->isFilterQueryValid($filter, $resourceClass, $existingFilter);
        }

        if (!$validQuery) {
            throw new BadRequestException();
        }

        return $this->decoratedCollectionDataProvider->getCollection($resourceClass, $operationName, $context);
    }

    private function isConstructionManagerQueryValid(ConstructionManager $manager, string $resourceClass, array $query)
    {
        if (ConstructionSite::class === $resourceClass || ConstructionManager::class) {
            return true;
        }

        if (!isset($query['constructionSite'])) {
            return false;
        }

        foreach ($manager->getConstructionSites() as $constructionSite) {
            if ($constructionSite->getId() === $query['constructionSite']) {
                return true;
            }
        }

        return false;
    }

    private function isCraftsmanQueryValid(Craftsman $craftsman, string $resourceClass, array $query)
    {
        $constructionSiteFilterValid = $this->searchFilterValid($query, 'constructionSite', $craftsman->getConstructionSite()->getId());
        if (!$constructionSiteFilterValid) {
            return false;
        }

        if (Map::class === $resourceClass || ConstructionManager::class === $resourceClass) {
            return true;
        }

        if (Issue::class === $resourceClass) {
            return $this->searchFilterValid($query, 'craftsman', [$craftsman->getId()]);
        }

        throw new BadRequestException();
    }

    private function isFilterQueryValid(Filter $filter, string $resourceClass, array $query)
    {
        $constructionSiteFilterValid = $this->searchFilterValid($query, 'constructionSite', $filter->getConstructionSite()->getId());
        if (!$constructionSiteFilterValid) {
            return false;
        }

        if (ConstructionManager::class === $resourceClass) {
            return true;
        }

        if (Map::class === $resourceClass) {
            return $this->searchFilterValid($query, 'id', $filter->getMapIds());
        }

        if (Craftsman::class === $resourceClass) {
            return $this->searchFilterValid($query, 'id', $filter->getCraftsmanIds()) &&
                $this->searchFilterValid($query, 'trade', $filter->getCraftsmanTrades());
        }

        if (Issue::class === $resourceClass) {
            return $this->searchFilterValid($query, 'map', $filter->getMapIds()) &&
                $this->searchFilterValid($query, 'craftsman', $filter->getCraftsmanIds()) &&
                $this->searchFilterValid($query, 'craftsman.trade', $filter->getCraftsmanTrades());
        }

        return false;
    }

    private function searchFilterValid(array $query, string $property, $restriction)
    {
        if (null === $restriction) {
            return true;
        }

        if (is_array($restriction)) {
            $singleFilterValid = isset($query[$property]) && in_array($query[$property], $restriction);
            $multipleFilterValid = isset($query[$property.'[]']) && empty(array_diff($restriction, $query[$property.'[]']));

            return $singleFilterValid || $multipleFilterValid;
        }

        return isset($query[$property]) && $query[$property] === $restriction;
    }
}
