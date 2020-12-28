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
        if ($value & Issue::STATE_REGISTERED) {
            $queryBuilder->andWhere($alias.'.registeredAt IS NOT NULL');
        } elseif ($value < Issue::STATE_REGISTERED) {
            $queryBuilder->andWhere($alias.'.registeredAt IS NULL');
        }

        if ($value & Issue::STATE_RESOLVED) {
            $queryBuilder->andWhere($alias.'.resolvedAt IS NOT NULL');
        } elseif ($value < Issue::STATE_RESOLVED) {
            $queryBuilder->andWhere($alias.'.resolvedAt IS NULL');
        }

        if ($value & Issue::STATE_CLOSED) {
            $queryBuilder->andWhere($alias.'.closedAt IS NOT NULL');
        } else {
            $queryBuilder->andWhere($alias.'.closedAt IS NULL');
        }
    }

    private function normalizeValue($value)
    {
        $intValue = (int) $value;
        if (Issue::STATE_CREATED <= $intValue && $intValue <= Issue::STATE_CLOSED) {
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
