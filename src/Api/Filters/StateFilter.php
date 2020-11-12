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
        }

        if ($value & Issue::STATE_SEEN) {
            $craftsmanAlias = $alias.'c';
            $queryBuilder->leftJoin($alias.'.craftsman', $craftsmanAlias);
            $queryBuilder->where($alias.'.registeredAt < '.$craftsmanAlias.'.lastOnlineVisit');
        }

        if ($value & Issue::STATE_RESPONDED) {
            $queryBuilder->andWhere($alias.'.respondedAt IS NOT NULL');
        }

        if ($value & Issue::STATE_REVIEWED) {
            $queryBuilder->andWhere($alias.'.reviewedAt IS NOT NULL');
        }
    }

    private function normalizeValue($value)
    {
        $intValue = (int) $value;
        if (Issue::STATE_CREATED <= $intValue && $intValue <= Issue::STATE_REVIEWED) {
            return $intValue;
        }

        $this->getLogger()->notice('Invalid filter ignored', [
            'exception' => new InvalidArgumentException(sprintf('Invalid value for '.self::STATE_PROPERTY_NAME.', expected in range '.Issue::STATE_CREATED.' - '.Issue::STATE_REVIEWED)),
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
                    'description' => 'Filter depending on state(s) of entry. Combine Created = 0, Registered = 1, Read = 2, Responded = 4 and Reviewed = 8.',
                    'name' => 'state',
                    'type' => 'integer',
                ],
            ],
        ];
    }
}
