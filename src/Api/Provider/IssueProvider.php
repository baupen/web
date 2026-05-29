<?php

namespace App\Api\Provider;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\Dto\CraftsmanStatisticsDto;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Service\Analysis\CraftsmanAnalysis;
use App\Service\AnalysisService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class IssueProvider implements ProviderInterface
{
    /**
     * @param ProviderInterface<Issue> $collectionProvider
     */
    public function __construct(
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        private RequestStack $requestStack
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        // custom controllers may need the filters again
        $currentRequest->attributes->set('filters', $context['filters']);

        return $this->collectionProvider->provide($operation, $uriVariables, $context);
    }
}
