<?php

namespace App\Api\Provider;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use App\Api\Dto\IssueSummaryDatedDto;
use App\Api\Provider\Traits\AuthenticatedProviderTrait;
use App\Api\Provider\Traits\CollectionProviderQueryBuilderTrait;
use App\Entity\Issue;
use App\Service\AnalysisService;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class IssueTimeseriesProvider implements ProviderInterface
{
    use AuthenticatedProviderTrait;
    use CollectionProviderQueryBuilderTrait;

    /**
     * @param QueryCollectionExtensionInterface[] $collectionExtensions
     */
    public function __construct(
        private readonly AnalysisService $analysisService,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger,
        ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        ManagerRegistry $managerRegistry,
        iterable $collectionExtensions = [],
        ?ContainerInterface $handleLinksLocator = null
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
        $this->resourceMetadataCollectionFactory = $resourceMetadataCollectionFactory;
        $this->handleLinksLocator = $handleLinksLocator;
        $this->managerRegistry = $managerRegistry;
        $this->collectionExtensions = $collectionExtensions;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $this->ensureGetCollectionOperation($operation);
        $this->ensureIssueCollectionAuthenticated($context);

        $queryBuilder = $this->provideQueryBuilder($operation, $uriVariables, $context);
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $lastPeriodEnd = new \DateTimeImmutable('today');
        $stepSize = new \DateInterval('P1D');
        $stepCount = 30;
        $issueAnalysisByTime = $this->analysisService->createIssueAnalysisByTime($rootAlias, $queryBuilder, $lastPeriodEnd, $stepSize, $stepCount);

        $summaries = [];
        foreach ($issueAnalysisByTime as $date => $issueAnalysis) {
            $summaries[] = IssueSummaryDatedDto::createFromIssueAnalysisWithDate($issueAnalysis, $date);
        }

        // want earliest (smallest) date first
        return array_reverse($summaries);
    }
}
