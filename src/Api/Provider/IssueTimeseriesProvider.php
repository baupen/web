<?php

namespace App\Api\Provider;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use App\Api\Dto\IssueSummaryDatedDto;
use App\Api\Dto\IssueSummaryDto;
use App\Api\Provider\Traits\CollectionProviderQueryBuilderTrait;
use App\Entity\Issue;
use App\Service\AnalysisService;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class IssueTimeseriesProvider implements ProviderInterface
{
    use CollectionProviderQueryBuilderTrait;

    /**
     * @param ProviderInterface<Issue> $collectionProvider
     * @param QueryCollectionExtensionInterface[] $collectionExtensions
     */
    public function __construct(
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        private AnalysisService $analysisService,
        ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        ManagerRegistry $managerRegistry,
        iterable $collectionExtensions = [],
        ?ContainerInterface $handleLinksLocator = null
    ) {
        $this->resourceMetadataCollectionFactory = $resourceMetadataCollectionFactory;
        $this->handleLinksLocator = $handleLinksLocator;
        $this->managerRegistry = $managerRegistry;
        $this->collectionExtensions = $collectionExtensions;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $queryBuilder = $this->provideQueryBuilder($operation, $uriVariables, $context);
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $lastPeriodEnd = new \DateTime('today');
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
