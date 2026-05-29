<?php

namespace App\Api\Provider;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\Dto\CraftsmanStatisticsDto;
use App\Api\Provider\Traits\AuthenticatedProviderTrait;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Map;
use App\Security\TokenTrait;
use App\Service\Analysis\CraftsmanAnalysis;
use App\Service\AnalysisService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class AuthenticatedCollectionProvider implements ProviderInterface
{
    use TokenTrait;
    use AuthenticatedProviderTrait;

    /**
     * @param ProviderInterface<Map> $collectionProvider
     */
    public function __construct(
        #[Autowire(service: CollectionProvider::class)] private ProviderInterface $collectionProvider,
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $resourceClass = $operation->getClass();
        if ($resourceClass === ConstructionSite::class) {
            $this->ensureConstructionManagersLimited($context);
        } else if ($resourceClass === ConstructionManager::class) {
            $this->ensureConstructionSitesLimited($context);
        } else {
            $this->ensureConstructionSiteAttributedCollectionFiltered($operation, $context);
        }

        return $this->collectionProvider->provide($operation, $uriVariables, $context);
    }
}
