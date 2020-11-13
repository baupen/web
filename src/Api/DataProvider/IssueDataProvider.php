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
use App\Entity\Issue;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class IssueDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var ContextAwareCollectionDataProviderInterface
     */
    private $decoratedCollectionDataProvider;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    private const ALREADY_CALLED = 'ISSUE_DATA_PROVIDER_ALREADY_CALLED';

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

        return Issue::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof ConstructionManager) {
            throw new NotImplementedException('need to filter access tokens');
            // restrict $context["filters"]
        }

        return $this->decoratedCollectionDataProvider->getCollection($resourceClass, $operationName, $context);
    }
}
