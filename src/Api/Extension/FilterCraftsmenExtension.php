<?php

namespace App\Api\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Api\Filters\RelatedConstructionManagerFilter;
use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Enum\Role;
use App\Security\TokenTrait;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class FilterCraftsmenExtension implements QueryCollectionExtensionInterface
{
    use TokenTrait;

    public function __construct(private Security $security, private TokenStorageInterface $tokenStorage)
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (Craftsman::class !== $resourceClass || $this->security->isGranted(Role::CONSTRUCTION_MANAGER->value)) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $whitelist = $this->getCraftsmanRestriction($token);
        if (null === $whitelist) {
            return;
        }

        $filters = $context['filters'] ?? [];
        $existingIdFilter = $filters['id'] ?? null;
        if ($existingIdFilter) {
            $whitelist = array_intersect($this->normalizeValue($existingIdFilter), $whitelist);
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere($alias . '.id IN (:whitelist)')
            ->setParameter(':whitelist', $whitelist);
    }

    /**
     * @param mixed $value
     * @return string[]
     */
    private function normalizeValue(mixed $value): array
    {
        return is_array($value) ? $value : [$value];
    }
}
