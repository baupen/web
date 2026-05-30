<?php

namespace App\Api\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Enum\Role;
use App\Security\TokenTrait;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class FilterConstructionSiteExtension implements QueryCollectionExtensionInterface
{
    use TokenTrait;

    public function __construct(private Security $security, private TokenStorageInterface $tokenStorage)
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $token = $this->tokenStorage->getToken();
        $constructionManager = $this->tryGetConstructionManager($token);

        if (ConstructionSite::class !== $resourceClass || $this->security->isGranted(Role::CONSTRUCTION_MANAGER->value)) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $constructionManager = $this->tryGetConstructionManager($token);
        assert($constructionManager instanceof ConstructionManager);

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere(sprintf(':construction_manager MEMBER OF %s.constructionManagers', $rootAlias));
        $queryBuilder->setParameter('construction_manager', $constructionManager);
    }
}
