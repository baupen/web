<?php


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
    private $managerRegistry;
    private $collectionExtensions;

    /**
     * @param QueryCollectionExtensionInterface[] $collectionExtensions
     */
    public function __construct(ManagerRegistry $managerRegistry, iterable $collectionExtensions = [])
    {
        $this->managerRegistry = $managerRegistry;
        $this->collectionExtensions = $collectionExtensions;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $this->managerRegistry->getManagerForClass($resourceClass) instanceof EntityManagerInterface && 'get' === $operationName;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
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
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);

            /** @noinspection PhpMethodParametersCountMismatchInspection */
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($resourceClass, $operationName, $context)) {
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                return $extension->getResult($queryBuilder, $resourceClass, $operationName, $context);
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }

    private function addSerializerRelevantJoins(string $resourceClass, string $alias, QueryBuilder $queryBuilder)
    {
        if ($resourceClass === ConstructionSite::class) {
            $queryBuilder->leftJoin($alias.'.image', 'i');
            $queryBuilder->addSelect('i');
            $queryBuilder->leftJoin($alias.'.constructionManagers', 'cm');
            $queryBuilder->addSelect('cm');
        } elseif ($resourceClass === Map::class) {
            $queryBuilder->leftJoin($alias.'.file', 'f');
            $queryBuilder->addSelect('f');
        } elseif ($resourceClass === Issue::class) {
            $queryBuilder->leftJoin($alias.'.image', 'i');
            $queryBuilder->addSelect('i');
        }
    }
}
