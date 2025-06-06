<?php

/*
 * This file is part of the baupen project.
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
use App\Entity\IssueEvent;
use App\Entity\Map;
use App\Security\TokenTrait;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthenticationAwareDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    use TokenTrait;

    private ContextAwareCollectionDataProviderInterface $decoratedCollectionDataProvider;

    private TokenStorageInterface $tokenStorage;

    private const ALREADY_CALLED = 'AUTHENTICATION_AWARE_DATA_PROVIDER_ALREADY_CALLED';

    /**
     * IssueReportDataProvider constructor.
     */
    public function __construct(ContextAwareCollectionDataProviderInterface $decoratedCollectionDataProvider, TokenStorageInterface $tokenStorage)
    {
        $this->decoratedCollectionDataProvider = $decoratedCollectionDataProvider;
        $this->tokenStorage = $tokenStorage;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        // Make sure we're not called twice
        return !isset($context[self::ALREADY_CALLED]);
    }

    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = []): iterable
    {
        $context[self::ALREADY_CALLED] = true;

        $token = $this->tokenStorage->getToken();

        $existingFilter = $context['filters'] ?? [];
        if (($constructionManager = $this->tryGetConstructionManager($token)) instanceof ConstructionManager) {
            $this->ensureConstructionManagerQueryValid($constructionManager, $resourceClass, $existingFilter);
        } elseif (($craftsman = $this->tryGetCraftsman($token)) instanceof Craftsman) {
            $this->ensureCraftsmanQueryValid($craftsman, $resourceClass, $existingFilter);
        } elseif (($filter = $this->tryGetFilter($token)) instanceof Filter) {
            $this->ensureFilterQueryValid($filter, $resourceClass, $existingFilter);
        } else {
            $this->ensureRenderQuery($resourceClass, $operationName, $existingFilter);
        }

        $context['filters'] = $existingFilter;

        return $this->decoratedCollectionDataProvider->getCollection($resourceClass, $operationName, $context);
    }

    private function ensureConstructionManagerQueryValid(ConstructionManager $manager, string $resourceClass, array &$query): void
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
                if (isset($query['constructionManagers.id'])) {
                    $this->ensureSearchFilterValid($query, 'constructionManagers.id', $manager->getId());
                } else {
                    $query['constructionManagers.id'] = [$manager->getId()];
                }

                return;
            }

            if (ConstructionManager::class === $resourceClass) {
                if (isset($query['constructionSites.id'])) {
                    $this->ensureArraySearchFilterValid($query, 'constructionSites.id', $ownConstructionSiteIds);
                } else {
                    $query['constructionSites.id'] = $ownConstructionSiteIds;
                }

                return;
            }
        }

        $this->ensureArraySearchFilterValid($query, 'constructionSite', $ownConstructionSiteIds);
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
            $this->ensureArraySearchFilterValid($query, 'craftsman', [$craftsman->getId()]);
            $this->ensureDeletedFilterValid($query, 'isDeleted', false);

            return;
        }

        if (IssueEvent::class === $resourceClass) {
            $this->ensureArraySearchFilterValid($query, 'createdBy', [$craftsman->getId()]);
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
            $this->ensureArraySearchFilterValid($query, 'id', $filter->getMapIds());

            return;
        }

        if (Craftsman::class === $resourceClass) {
            $this->ensureArraySearchFilterValid($query, 'id', $filter->getCraftsmanIds());

            return;
        }

        if (Issue::class === $resourceClass) {
            $this->ensureBooleanSearchFilterValid($query, 'isMarked', $filter->getIsMarked());
            $this->ensureBooleanSearchFilterValid($query, 'wasAddedWithClient', $filter->getWasAddedWithClient());

            $this->ensureArraySearchFilterValid($query, 'number', $filter->getNumbers());
            $this->ensureSearchFilterValid($query, 'description', $filter->getDescription());

            $this->ensureSearchFilterValid($query, 'state', $filter->getState());
            $this->ensureArraySearchFilterValid($query, 'craftsman', $filter->getCraftsmanIds());
            $this->ensureArraySearchFilterValid($query, 'map', $filter->getMapIds());

            $this->ensureDateTimeSearchFilterValid($query, 'deadline', 'before', $filter->getDeadlineBefore());
            $this->ensureDateTimeSearchFilterValid($query, 'deadline', 'after', $filter->getDeadlineAfter());

            $this->ensureSearchFilterValid($query, 'createdBy', $filter->getCreatedBy());
            $this->ensureSearchFilterValid($query, 'registeredBy', $filter->getRegisteredBy());
            $this->ensureSearchFilterValid($query, 'closedBy', $filter->getClosedBy());

            $this->ensureDateTimeSearchFilterValid($query, 'createdAt', 'before', $filter->getCreatedAtBefore());
            $this->ensureDateTimeSearchFilterValid($query, 'createdAt', 'after', $filter->getCreatedAtAfter());
            $this->ensureDateTimeSearchFilterValid($query, 'registeredAt', 'before', $filter->getRegisteredAtBefore());
            $this->ensureDateTimeSearchFilterValid($query, 'registeredAt', 'after', $filter->getRegisteredAtAfter());
            $this->ensureDateTimeSearchFilterValid($query, 'resolvedAt', 'before', $filter->getResolvedAtBefore());
            $this->ensureDateTimeSearchFilterValid($query, 'resolvedAt', 'after', $filter->getResolvedAtAfter());
            $this->ensureDateTimeSearchFilterValid($query, 'closedAt', 'before', $filter->getClosedAtBefore());
            $this->ensureDateTimeSearchFilterValid($query, 'closedAt', 'after', $filter->getClosedAtAfter());

            return;
        }

        throw new BadRequestException('You are not allowed to query this resource');
    }

    private function ensureSearchFilterValid(array $query, string $property, int|string|null $restriction): void
    {
        if (null !== $restriction) {
            if (!isset($query[$property])) {
                throw new BadRequestException($property.' filter missing.');
            }

            // must use single equal sign, for int restriction (as query[property] will be string)
            if ($query[$property] != $restriction) {
                throw new BadRequestException($property.' filter value '.$query[$property].' not equal to '.$restriction.'.');
            }
        }
    }

    private function ensureBooleanSearchFilterValid(array $query, string $property, ?bool $restriction): void
    {
        if (null !== $restriction) {
            if (!isset($query[$property])) {
                throw new BadRequestException($property.' filter missing.');
            }

            if ($restriction && \in_array($query[$property], [true, 'true', '1'], true)) {
                return;
            }

            if (!$restriction && \in_array($query[$property], [false, 'false', '0'], true)) {
                return;
            }

            throw new BadRequestException($property.' filter missing or value not equal to '.$restriction.'.');
        }
    }

    private function ensureArraySearchFilterValid(array $query, string $property, ?array $restriction): void
    {
        if (null !== $restriction) {
            if (!isset($query[$property])) {
                throw new BadRequestException($property.' filter missing.');
            }

            if (is_array($query[$property])) {
                if ([] !== array_diff($query[$property], $restriction)) {
                    throw new BadRequestException($property.' filter value '.implode($query[$property]).' not equal '.implode($restriction).'.');
                }
            } elseif (!in_array($query[$property], $restriction)) {
                throw new BadRequestException($property.' filter value '.$query[$property].' not in '.implode($restriction).'.');
            }
        }
    }

    private function ensureDateTimeSearchFilterValid(array $query, string $property, string $timing, ?\DateTime $restriction): void
    {
        if ($restriction instanceof \DateTime) {
            if (!isset($query[$property]) || !isset($query[$property][$timing])) {
                throw new BadRequestException($property.' filter missing.');
            }

            $parsedValue = str_replace(' ', '+', $query[$property][$timing]);
            $value = new \DateTime($parsedValue);
            if ($value != $restriction) {
                throw new BadRequestException($property.' filter value '.$value->format('c').' not in equal '.$restriction->format('c').'.');
            }
        }
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

    private function ensureRenderQuery(string $resourceClass, ?string $operationName, array $existingFilter): void
    {
        if (Issue::class === $resourceClass && 'get_render' === $operationName && (isset($existingFilter['map']) && !isset($existingFilter['map[]']))) {
            return;
        }

        throw new HttpException(Response::HTTP_FORBIDDEN);
    }
}
