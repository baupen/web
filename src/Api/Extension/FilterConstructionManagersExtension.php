<?php

namespace App\Api\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Api\Filters\RelatedConstructionManagerFilter;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Enum\Role;
use App\Security\TokenTrait;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final readonly class FilterConstructionManagersExtension implements QueryCollectionExtensionInterface
{
    use TokenTrait;

    public function __construct(private Security $security, private TokenStorageInterface $tokenStorage, private RelatedConstructionManagerFilter $relatedConstructionManagerFilter)
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (ConstructionManager::class !== $resourceClass || $this->security->isGranted(Role::CONSTRUCTION_MANAGER->value)) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $filter = ["constructionSites.id" => $this->getConstructionSiteRestriction($token)];
        $context['filters'] = $filter;
        $this->relatedConstructionManagerFilter->apply($queryBuilder, $queryNameGenerator, $resourceClass, $operation, $context);
    }
}
