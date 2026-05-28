<?php

namespace App\Api\Filters;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Issue;
use Doctrine\ORM\QueryBuilder;

readonly class StateFilter implements FilterInterface
{
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $parameter = $context['parameter'] ?? null;
        $value = $this->normalizeValue($parameter?->getValue());
        if (null === $value) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $orQueries = [];
        if (($value & Issue::STATE_CREATED) !== 0) {
            $orQueries[] = $alias . '.registeredAt IS NULL AND ' . $alias . '.resolvedAt IS NULL AND ' . $alias . '.closedAt IS NULL';
        }
        if (($value & Issue::STATE_REGISTERED) !== 0) {
            $orQueries[] = $alias . '.registeredAt IS NOT NULL AND ' . $alias . '.resolvedAt IS NULL AND ' . $alias . '.closedAt IS NULL';
        }
        if (($value & Issue::STATE_RESOLVED) !== 0) {
            $orQueries[] = $alias . '.resolvedAt IS NOT NULL AND ' . $alias . '.closedAt IS NULL';
        }
        if (($value & Issue::STATE_CLOSED) !== 0) {
            $orQueries[] = $alias . '.closedAt IS NOT NULL';
        }

        if ([] !== $orQueries) {
            $queryBuilder->andWhere('(' . implode(') OR (', $orQueries) . ')');
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'state' => [
                'type' => 'integer',
                'property' => 'state',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter depending on state(s) of entry. Combine Created = 0, Registered = 1, Read = 2, Resolved = 4 and Closed = 8.',
                    'name' => 'state',
                    'type' => 'integer',
                ],
            ],
        ];
    }

    private function normalizeValue($value): ?int
    {
        $intValue = (int)$value;
        $maxCombination = Issue::STATE_CREATED | Issue::STATE_REGISTERED | Issue::STATE_RESOLVED | Issue::STATE_CLOSED;
        if (Issue::STATE_CREATED <= $intValue && $intValue <= $maxCombination) {
            return $intValue;
        }

        return null;
    }
}
