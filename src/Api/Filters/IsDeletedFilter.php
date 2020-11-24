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
use Doctrine\ORM\QueryBuilder;

class IsDeletedFilter extends AbstractContextAwareFilter
{
    public const IS_DELETED_PROPERTY_NAME = 'isDeleted';

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        // otherwise filter is applied to order and page as well
        if (!$this->isPropertyEnabled($property, $resourceClass)) {
            return;
        }

        $value = self::normalizeValue($value);
        if (null === $value) {
            $this->getLogger()->notice('Invalid filter ignored', [
                'exception' => new InvalidArgumentException(sprintf('Invalid value for '.self::IS_DELETED_PROPERTY_NAME.', expected one of ( "%s" )',
                    self::IS_DELETED_PROPERTY_NAME,
                    implode('" | "', ['true', 'false', '1', '0'])
                )),
            ]);

            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        if ($value) {
            $queryBuilder->andWhere($alias.'.deletedAt IS NOT NULL');
        } else {
            $queryBuilder->andWhere($alias.'.deletedAt IS NULL');
        }
    }

    public static function normalizeValue($value)
    {
        if (\in_array($value, [true, 'true', '1'], true)) {
            return true;
        }

        if (\in_array($value, [false, 'false', '0'], true)) {
            return false;
        }

        return null;
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
}
