<?php

namespace App\Api\Provider;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\Dto\CraftsmanStatisticsDto;
use App\Api\Provider\Traits\AuthenticatedProviderTrait;
use App\Entity\Craftsman;
use App\Service\Analysis\CraftsmanAnalysis;
use App\Service\AnalysisService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class CraftsmanStatisticsProvider implements ProviderInterface
{
    use AuthenticatedProviderTrait;

    /**
     * @param ProviderInterface<Craftsman> $collectionProvider
     */
    public function __construct(
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        private AnalysisService $analysisService,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->ensureConstructionSiteAttributedCollectionFiltered($operation, $context);

        $craftsmen = $this->collectionProvider->provide($operation, $uriVariables, $context);
        $craftsmanAnalysisByCraftsman = $this->analysisService->createCraftsmanAnalysisByCraftsman($craftsmen);

        return array_map(
            static fn (CraftsmanAnalysis $craftsmanAnalysis): CraftsmanStatisticsDto => CraftsmanStatisticsDto::create($craftsmanAnalysis),
            $craftsmanAnalysisByCraftsman
        );
    }
}
