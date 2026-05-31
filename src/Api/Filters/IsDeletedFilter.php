<?php

namespace App\Api\Filters;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

readonly class IsDeletedFilter implements FilterInterface
{
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $filters = $context['filters'] ?? [];
        $isDeleted = $filters['isDeleted'] ?? null;
        $value = $this->normalizeValue($isDeleted);
        if (null === $value) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        if ($value) {
            $queryBuilder->andWhere($alias . '.deletedAt IS NOT NULL');
        } else {
            $queryBuilder->andWhere($alias . '.deletedAt IS NULL');
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'isDeleted' => [
                'type' => 'boolean',
                'property' => 'isDeleted',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter depending if entry is soft-deleted.',
                    'name' => 'isDeleted',
                    'type' => 'boolean',
                ],
            ],
        ];
    }

    private function normalizeValue(mixed $value): ?bool
    {
        if (\in_array($value, [true, 'true', '1'], true)) {
            return true;
        }

        if (\in_array($value, [false, 'false', '0'], true)) {
            return false;
        }

        return null;
    }
}
