<?php

namespace App\Api\Provider;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\Provider\Traits\AuthenticatedProviderTrait;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\IssueEvent;
use App\Entity\Map;
use App\Security\TokenTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->ensureGetCollectionOperation($operation);

        $resourceClass = $operation->getClass();
        if ($resourceClass === ConstructionSite::class) {
            $this->ensureConstructionSiteFilteredByManagers($context);
        } elseif ($resourceClass === ConstructionManager::class) {
            $this->ensureConstructionManagersFilteredBySites($context);
        } elseif ($resourceClass === IssueEvent::class) {
            $this->ensureIssueEventCollectionAuthenticated($context);
        } else {
            $this->ensureConstructionSiteAttributedCollectionFiltered($context);
        }

        return $this->collectionProvider->provide($operation, $uriVariables, $context);
    }
}
