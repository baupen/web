<?php

namespace App\Api\Provider;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\Dto\CraftsmanStatisticsDto;
use App\Api\Provider\Traits\AuthenticatedProviderTrait;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Service\Analysis\CraftsmanAnalysis;
use App\Service\AnalysisService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class IssueCollectionProvider implements ProviderInterface
{
    use AuthenticatedProviderTrait;

    /**
     * @param ProviderInterface<Issue> $collectionProvider
     */
    public function __construct(
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        private RequestStack $requestStack,
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->ensureIssueCollectionAuthenticated($operation, $context);

        // store filters in request for custom controllers
        $currentRequest = $this->requestStack->getCurrentRequest();
        $currentRequest->attributes->set('filters', $context['filters']);

        return $this->collectionProvider->provide($operation, $uriVariables, $context);
    }
}
