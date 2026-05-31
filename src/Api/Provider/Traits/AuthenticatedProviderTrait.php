<?php

namespace App\Api\Provider\Traits;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

trait AuthenticatedProviderTrait
{
    private readonly TokenStorageInterface $tokenStorage;
    private readonly LoggerInterface $logger;

    private function ensureConstructionSiteFilteredByManagers(array $context): void
    {
        $token = $this->tokenStorage->getToken();
        $filters = $context['filters'] ?? [];
        $constructionManager = $this->tryGetConstructionManager($token);
        if (!$constructionManager) {
            throw new BadRequestException('You are not allowed query this collection.');
        }

        if (!$constructionManager->getCanAssociateSelf()) {
            if (isset($filters['constructionManagers.id'])) {
                $this->ensureSearchFilterValid($filters, 'constructionManagers.id', $constructionManager->getId());
            } else {
                // this is fine; we filter afterwards in the corresponding extension
                // but should not rely on this, incorrect way to use REST API
                $this->logger->warning('Construction manager restriction not applied to construction site collection.');
            }
        }
    }

    private function ensureConstructionManagersFilteredBySites(array $context): void
    {
        $token = $this->tokenStorage->getToken();
        $constructionSiteRestriction = $this->getConstructionSiteRestriction($token);
        if (!$constructionSiteRestriction) {
            return;
        }

        $existingFilter = $context['filters'] ?? [];
        if (isset($existingFilter['constructionSites.id'])) {
            $this->ensureArraySearchFilterValid($existingFilter, 'constructionSites.id', $constructionSiteRestriction);
        } else {
            // this is fine; we filter afterwards in the corresponding extension
            // but should not rely on this, incorrect way to use REST API
            $this->logger->warning('Construction site restriction not applied to construction manager collection.');
        }
    }

    private function ensureConstructionSiteAttributedCollectionFiltered(Operation $operation, array $context): void
    {
        // check properly filtered
        if (!$operation instanceof GetCollection) {
            throw new BadRequestException('Only collection operations are supported by this provider.');
        }

        $token = $this->tokenStorage->getToken();
        $constructionSiteRestriction = $this->getConstructionSiteRestriction($token);

        $existingFilter = $context['filters'] ?? [];
        $this->ensureArraySearchFilterValid($existingFilter, 'constructionSite', $constructionSiteRestriction);
    }

    private function ensureIssueCollectionAuthenticated(Operation $operation, array $context): void
    {
        $this->ensureConstructionSiteAttributedCollectionFiltered($operation, $context);
        $this->ensureIssueFilterAppliedIfFilterAuthentication($context);
    }

    private function ensureIssueFilterAppliedIfFilterAuthentication(array $context): void
    {
        $token = $this->tokenStorage->getToken();
        $filter = $this->tryGetFilter($token);
        if (!$filter) {
            return;
        }

        $query = $context['filters'] ?? [];

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
    }

    private function ensureSearchFilterValid(array $query, string $property, int|string|null $restriction): void
    {
        if (!$restriction) {
            return;
        }

        if (!isset($query[$property])) {
            throw new BadRequestException($property . ' filter missing.');
        }

        // must use single equal sign, for int restriction (as query[property] will be string)
        if ($query[$property] != $restriction) {
            throw new BadRequestException($property . ' filter value ' . $query[$property] . ' not equal to ' . $restriction . '.');
        }
    }

    private function ensureBooleanSearchFilterValid(array $query, string $property, ?bool $restriction): void
    {
        if ($restriction === null) {
            return;
        }

        if (!isset($query[$property])) {
            throw new BadRequestException($property . ' filter missing.');
        }

        if ($restriction && \in_array($query[$property], [true, 'true', '1'], true)) {
            return;
        }

        if (!$restriction && \in_array($query[$property], [false, 'false', '0'], true)) {
            return;
        }

        throw new BadRequestException($property . ' filter missing or value not equal to ' . $restriction . '.');
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

    private function ensureDateTimeSearchFilterValid(array $query, string $property, string $timing, ?\DateTime $restriction): void
    {
        if (!$restriction) {
            return;
        }

        if (!isset($query[$property]) || !isset($query[$property][$timing])) {
            throw new BadRequestException($property . ' filter missing.');
        }

        $parsedValue = str_replace(' ', '+', $query[$property][$timing]);
        $value = new \DateTime($parsedValue);
        if ($value != $restriction) {
            throw new BadRequestException($property . ' filter value ' . $value->format('c') . ' not in equal ' . $restriction->format('c') . '.');
        }
    }
}
