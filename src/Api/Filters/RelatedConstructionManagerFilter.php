<?php

declare(strict_types=1);

namespace App\Api\Filters;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\ConstructionManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

readonly class RelatedConstructionManagerFilter implements FilterInterface
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $parameter = $context['parameter'] ?? null;
        $value = $this->normalizeValue($parameter?->getValue());

        // get whitelist of connected construction managers
        $repository = $this->managerRegistry->getRepository(ConstructionManager::class);
        $whitelist = $repository->getRelatedConstructionManagers($value);

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere($alias . '.id IN (:whitelist)')
            ->setParameter(':whitelist', $whitelist);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'constructionSite.id' => [
                'type' => 'iri',
                'property' => 'constructionSite',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter by construction managers that are (or used to be) involved on the construction site.',
                    'name' => 'state',
                    'type' => 'iri',
                ],
            ],
        ];
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
