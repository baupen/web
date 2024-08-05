<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\Exception\RuntimeException;
use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Entity\Map;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class SerializerAwareDataProvider
{
    private ManagerRegistry $managerRegistry;
    private iterable $collectionExtensions;

    /**
     * @param QueryCollectionExtensionInterface[] $collectionExtensions
     */
    public function __construct(ManagerRegistry $managerRegistry, iterable $collectionExtensions = [])
    {
        $this->managerRegistry = $managerRegistry;
        $this->collectionExtensions = $collectionExtensions;
    }

    public function supports(string $resourceClass, ?string $operationName = null): bool
    {
        return $this->managerRegistry->getManagerForClass($resourceClass) instanceof EntityManagerInterface && 'get' === $operationName;
    }

    /**
     * @throws RuntimeException
     */
    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = [])
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->managerRegistry->getManagerForClass($resourceClass);

        $repository = $manager->getRepository($resourceClass);
        if (!method_exists($repository, 'createQueryBuilder')) {
            throw new RuntimeException('The repository class must have a "createQueryBuilder" method.');
        }

        $alias = 'o';
        $queryBuilder = $repository->createQueryBuilder($alias);
        $this->addSerializerRelevantJoins($resourceClass, $alias, $queryBuilder);
        $queryNameGenerator = new QueryNameGenerator();
        // code taken from ApiPlatform\Core\Bridge\Doctrine\Orm\CollectionDataProvider
        foreach ($this->collectionExtensions as $extension) {
            /* @noinspection PhpMethodParametersCountMismatchInspection */
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);

            /* @noinspection PhpMethodParametersCountMismatchInspection */
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($resourceClass, $operationName, $context)) {
                /* @noinspection PhpMethodParametersCountMismatchInspection */
                return $extension->getResult($queryBuilder, $resourceClass, $operationName, $context);
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }

    private function addSerializerRelevantJoins(string $resourceClass, string $alias, QueryBuilder $queryBuilder): void
    {
        if (ConstructionSite::class === $resourceClass) {
            $queryBuilder->leftJoin($alias.'.image', 'i');
            $queryBuilder->addSelect('i');
            $queryBuilder->leftJoin($alias.'.constructionManagers', 'cm');
            $queryBuilder->addSelect('cm');
        } elseif (Map::class === $resourceClass) {
            $queryBuilder->leftJoin($alias.'.file', 'f');
            $queryBuilder->addSelect('f');
        } elseif (Issue::class === $resourceClass) {
            $queryBuilder->leftJoin($alias.'.image', 'i');
            $queryBuilder->addSelect('i');
        }
    }
}
