<?php

namespace App\Api\Provider;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\Dto\CraftsmanStatisticsDto;
use App\Api\Provider\Traits\AuthenticatedProviderTrait;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Map;
use App\Security\TokenTrait;
use App\Service\Analysis\CraftsmanAnalysis;
use App\Service\AnalysisService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class AuthenticatedConstructionSiteProvider implements ProviderInterface
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
        // check properly filtered
        if (!$operation instanceof GetCollection) {
            throw new BadRequestException('Only collection operations are supported by this provider.');
        }
        $this->ensureConstructionSiteFiltered($context);

        return $this->collectionProvider->provide($operation, $uriVariables, $context);
    }
}
