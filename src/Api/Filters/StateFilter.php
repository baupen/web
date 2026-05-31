<?php

namespace App\Api\Filters;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Enum\IssueState;
use Doctrine\ORM\QueryBuilder;

readonly class StateFilter implements FilterInterface
{
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $filters = $context['filters'] ?? [];
        $state = $filters['isDeleted'] ?? null;
        $value = $this->normalizeValue($state);
        if (null === $value) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $orQueries = [];
        if (($value & IssueState::CREATED->value) !== 0) {
            $orQueries[] = $alias . '.registeredAt IS NULL AND ' . $alias . '.resolvedAt IS NULL AND ' . $alias . '.closedAt IS NULL';
        }
        if (($value & IssueState::REGISTERED->value) !== 0) {
            $orQueries[] = $alias . '.registeredAt IS NOT NULL AND ' . $alias . '.resolvedAt IS NULL AND ' . $alias . '.closedAt IS NULL';
        }
        if (($value & IssueState::RESOLVED->value) !== 0) {
            $orQueries[] = $alias . '.resolvedAt IS NOT NULL AND ' . $alias . '.closedAt IS NULL';
        }
        if (($value & IssueState::CLOSED->value) !== 0) {
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
        $maxCombination = IssueState::CREATED->value | IssueState::REGISTERED->value | IssueState::RESOLVED->value | IssueState::CLOSED->value;
        if (IssueState::CREATED->value <= $intValue && $intValue <= $maxCombination) {
            return $intValue;
        }

        return null;
    }
}
