<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\DataProvider\Base;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

abstract class NoPaginationDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $managerRegistry;
    private $collectionExtensions;

    /**
     * @param ContextAwareQueryCollectionExtensionInterface[] $collectionExtensions
     */
    public function __construct(ManagerRegistry $managerRegistry, iterable $collectionExtensions = [])
    {
        $this->managerRegistry = $managerRegistry;
        $this->collectionExtensions = $collectionExtensions;
    }

    protected function getCollectionQueryBuilerWithoutPagination(string $resourceClass, ?string $operationName = null, array $context = [])
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->managerRegistry->getManagerForClass($resourceClass);

        $repository = $manager->getRepository($resourceClass);
        $queryBuilder = $repository->createQueryBuilder('o');
        $queryNameGenerator = new QueryNameGenerator();
        foreach ($this->collectionExtensions as $extension) {
            if ($extension instanceof QueryResultCollectionExtensionInterface) {
                continue;
            }

            $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);
        }

        return $queryBuilder;
    }
}
