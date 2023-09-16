<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Filters;

use ApiPlatform\Core\Bridge\Doctrine\Common\Filter\OrderFilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Extends the original order filter with NULLS_ALWAYS_LAST and NULLS_ALWAYS_FIRST functionality.
 */
class PatchedOrderFilter extends OrderFilter implements OrderFilterInterface
{
    public const NULLS_ALWAYS_LAST = 'nulls_always_last';
    public const NULLS_ALWAYS_FIRST = 'nulls_always_first';

    protected function normalizeValue($value, string $property): ?string
    {
        if (empty($value) && null !== $defaultDirection = $this->getProperties()[$property]['default_direction'] ?? null) {
            // fallback to default direction
            $value = $defaultDirection;
        }

        $value = strtoupper($value);
        if (!\in_array($value, [self::DIRECTION_ASC, self::DIRECTION_DESC], true)) {
            return null;
        }

        return $value;
    }

    protected function filterProperty(string $property, $direction, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $nullsComparison = $this->properties[$property]['nulls_comparison'] ?? null;
        if (null === $nullsComparison || !in_array($nullsComparison, [self::NULLS_ALWAYS_FIRST, self::NULLS_ALWAYS_LAST])) {
            parent::filterProperty($property, $direction, $queryBuilder, $queryNameGenerator, $resourceClass, $operationName);

            return;
        }

        if (!$this->isPropertyEnabled($property, $resourceClass) || !$this->isPropertyMapped($property, $resourceClass)) {
            return;
        }

        $direction = $this->normalizeValue($direction, $property);
        if (null === $direction) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $field = $property;

        if ($this->isPropertyNested($property, $resourceClass)) {
            [$alias, $field] = $this->addJoinsForNestedProperty($property, $alias, $queryBuilder, $queryNameGenerator, $resourceClass, Join::LEFT_JOIN);
        }

        $nullRankHiddenField = sprintf('_%s_%s_null_rank', $alias, $field);

        $queryBuilder->addSelect(sprintf('CASE WHEN %s.%s IS NULL THEN 0 ELSE 1 END AS HIDDEN %s', $alias, $field, $nullRankHiddenField));
        $queryBuilder->addOrderBy($nullRankHiddenField, self::NULLS_ALWAYS_FIRST === $nullsComparison ? 'ASC' : 'DESC');

        $queryBuilder->addOrderBy(sprintf('%s.%s', $alias, $field), $direction);
    }
}
