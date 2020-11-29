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
        if (($constructionManager = $this->tryGetConstructionManager($token))) {
            $this->ensureConstructionManagerQueryValid($constructionManager, $resourceClass, $existingFilter);
        } elseif ($craftsman = $this->tryGetCraftsman($token)) {
            $this->ensureCraftsmanQueryValid($craftsman, $resourceClass, $existingFilter);
        } elseif ($filter = $this->tryGetFilter($token)) {
            $this->ensureFilterQueryValid($filter, $resourceClass, $existingFilter);
        } else {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        return $this->decoratedCollectionDataProvider->getCollection($resourceClass, $operationName, $context);
    }

    private function ensureConstructionManagerQueryValid(ConstructionManager $manager, string $resourceClass, array $query): void
    {
        if (ConstructionSite::class === $resourceClass || ConstructionManager::class === $resourceClass) {
            return;
        }

        if (!isset($query['constructionSite'])) {
            throw new BadRequestException('constructionSite filter missing.');
        }

        foreach ($manager->getConstructionSites() as $constructionSite) {
            if ($constructionSite->getId() === $query['constructionSite']) {
                return;
            }
        }

        throw new BadRequestException('You are not allowed to query a construction site you are not part of.');
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
            $this->ensureSearchFilterValid($query, 'trade', $filter->getCraftsmanTrades());

            return;
        }

        if (Issue::class === $resourceClass) {
            $this->ensureSearchFilterValid($query, 'map', $filter->getMapIds());
            $this->ensureSearchFilterValid($query, 'craftsman', $filter->getCraftsmanIds());
            $this->ensureSearchFilterValid($query, 'craftsman.trade', $filter->getCraftsmanTrades());

            // TODO: Fully implement filter properties #350
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
            $singleFilterValid = isset($query[$property]) && in_array($query[$property], $restriction);
            $multipleFilterValid = isset($query[$property.'[]']) && empty(array_diff($restriction, $query[$property.'[]']));

            if ($singleFilterValid || $multipleFilterValid) {
                return;
            }

            throw new BadRequestException($property.' filter missing or value no one of '.implode(', ', $restriction).'.');
        }

        if (isset($query[$property]) && $query[$property] === $restriction) {
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
}