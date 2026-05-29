<?php

namespace App\Api\Provider;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use ApiPlatform\State\ProviderInterface;
use App\Api\Dto\IssueGroupDto;
use App\Api\Dto\IssueSummaryDto;
use App\Api\Provider\Traits\CollectionProviderQueryBuilderTrait;
use App\Entity\Issue;
use App\Entity\Map;
use App\Extension\UTCDateTimeType;
use App\Service\AnalysisService;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class IssueGroupProvider implements ProviderInterface
{
    use CollectionProviderQueryBuilderTrait;

    /**
     * @param ProviderInterface<Issue> $collectionProvider
     * @param QueryCollectionExtensionInterface[] $collectionExtensions
     */
    public function __construct(
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        private AnalysisService $analysisService,
        private RequestStack $requestStack,
        private IriConverterInterface $iriConverter,
        ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        ManagerRegistry $managerRegistry,
        iterable $collectionExtensions = [],
        ?ContainerInterface $handleLinksLocator = null,
    ) {
        $this->resourceMetadataCollectionFactory = $resourceMetadataCollectionFactory;
        $this->handleLinksLocator = $handleLinksLocator;
        $this->managerRegistry = $managerRegistry;
        $this->collectionExtensions = $collectionExtensions;
    }

    /**
     * @param Operation $operation
     * @return IssueGroupDto[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        $group = $currentRequest->query->get('group');
        if ('map' !== $group) {
            throw new BadRequestException('The group ' . $group . ' is unexpected.');
        }

        $queryBuilder = $this->provideQueryBuilder($operation, $uriVariables, $context);
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $validIssueIdResult = $queryBuilder->addSelect($rootAlias . '.id')->getQuery()->getResult();
        $validIssueIds = [];
        foreach ($validIssueIdResult as $entry) {
            $validIssueIds[] = $entry['id'];
        }

        $issueRepository = $this->managerRegistry->getRepository(Issue::class);
        $groupByQuery = $issueRepository
            ->createQueryBuilder('i')
            ->addSelect(['IDENTITY(i.map)', 'COUNT(i)', 'MIN(i.deadline)'])
            ->where('i.id IN (:ids)')
            ->setParameter(':ids', $validIssueIds)
            ->groupBy('i.map');

        $issueGroupResults = $groupByQuery->getQuery()->getResult();
        $issueGroups = [];
        foreach ($issueGroupResults as $issueGroupResult) {
            // indexes are 1-based
            $iri = $this->iriConverter->getIriFromResource(Map::class, UrlGeneratorInterface::ABS_PATH, null, ['uri_variables' => ['id' => $issueGroupResult[1]]]);
            $count = $issueGroupResult[2];
            $earliestDeadline = UTCDateTimeType::tryParseDateTime($issueGroupResult[3]);
            $issueGroups[] = IssueGroupDto::create($iri, $count, $earliestDeadline);
        }

        return $issueGroups;
    }
}
