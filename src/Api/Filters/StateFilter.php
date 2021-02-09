<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Filters;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Issue;
use Doctrine\ORM\QueryBuilder;

class StateFilter extends AbstractContextAwareFilter
{
    public const STATE_PROPERTY_NAME = 'state';

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        // otherwise filter is applied to order and page as well
        if (!$this->isPropertyEnabled($property, $resourceClass)) {
            return;
        }

        $value = $this->normalizeValue($value);
        if (null === $value) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $orQueries = [];
        if ($value & Issue::STATE_CREATED) {
            $orQueries[] = $alias.'.registeredAt IS NULL AND '.$alias.'.resolvedAt IS NULL AND '.$alias.'.closedAt IS NULL';
        }
        if ($value & Issue::STATE_REGISTERED) {
            $orQueries[] = $alias.'.registeredAt IS NOT NULL AND '.$alias.'.resolvedAt IS NULL AND '.$alias.'.closedAt IS NULL';
        }
        if ($value & Issue::STATE_RESOLVED) {
            $orQueries[] = $alias.'.resolvedAt IS NOT NULL AND '.$alias.'.closedAt IS NULL';
        }
        if ($value & Issue::STATE_CLOSED) {
            $orQueries[] = $alias.'.closedAt IS NOT NULL';
        }

        if (count($orQueries) > 0) {
            $queryBuilder->andWhere('('.implode(') OR (', $orQueries).')');
        }
    }

    private function normalizeValue($value)
    {
        $intValue = (int) $value;
        $maxCombination = Issue::STATE_CREATED | Issue::STATE_REGISTERED | Issue::STATE_RESOLVED | Issue::STATE_CLOSED;
        if (Issue::STATE_CREATED <= $intValue && $intValue <= $maxCombination) {
            return $intValue;
        }

        $this->getLogger()->notice('Invalid filter ignored', [
            'exception' => new InvalidArgumentException(sprintf('Invalid value for '.self::STATE_PROPERTY_NAME.', expected in range '.Issue::STATE_CREATED.' - '.Issue::STATE_CLOSED)),
        ]);

        return null;
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
}
