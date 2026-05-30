<?php

namespace App\Api\Provider;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use App\Api\Dto\IssueSummaryDto;
use App\Api\Provider\Traits\AuthenticatedProviderTrait;
use App\Api\Provider\Traits\CollectionProviderQueryBuilderTrait;
use App\Entity\Issue;
use App\Service\AnalysisService;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class IssueSummaryProvider implements ProviderInterface
{
    use AuthenticatedProviderTrait;
    use CollectionProviderQueryBuilderTrait;

    /**
     * @param ProviderInterface<Issue> $collectionProvider
     * @param QueryCollectionExtensionInterface[] $collectionExtensions
     */
    public function __construct(
        #[Autowire(service: CollectionProvider::class)] private readonly ProviderInterface $collectionProvider,
        private readonly AnalysisService $analysisService,
        TokenStorageInterface $tokenStorage,
        ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        ManagerRegistry $managerRegistry,
        iterable $collectionExtensions = [],
        ?ContainerInterface $handleLinksLocator = null
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->resourceMetadataCollectionFactory = $resourceMetadataCollectionFactory;
        $this->handleLinksLocator = $handleLinksLocator;
        $this->managerRegistry = $managerRegistry;
        $this->collectionExtensions = $collectionExtensions;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): IssueSummaryDto
    {
        $this->ensureIssueCollectionAuthenticated($operation, $context);

        $queryBuilder = $this->provideQueryBuilder($operation, $uriVariables, $context);
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $issueAnalysis = $this->analysisService->createIssueAnalysis($rootAlias, $queryBuilder);
        return IssueSummaryDto::create($issueAnalysis);
    }
}
