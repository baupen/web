<?php

namespace App\Api\DataProvider\Base;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Doctrine\Common\State\LinksHandlerLocatorTrait;
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\State\LinksHandlerTrait;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\Exception\RuntimeException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\Util\StateOptionsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;

abstract readonly class NoPaginationDataProvider
{
    /**
     * @param QueryCollectionExtensionInterface[] $collectionExtensions
     */
    public function __construct(private ManagerRegistry $managerRegistry, private iterable $collectionExtensions = [])
    {
    }

    public function getQueryBuilder(Operation $operation, array $uriVariables = [], array $context = []): QueryBuilder
    {
        $entityClass = $operation->getClass();

        /** @var EntityManagerInterface $manager */
        $manager = $this->managerRegistry->getManagerForClass($entityClass);

        $repository = $manager->getRepository($entityClass);
        if (!method_exists($repository, 'createQueryBuilder')) {
            throw new RuntimeException('The repository class must have a "createQueryBuilder" method.');
        }

        $queryBuilder = $repository->createQueryBuilder('o');
        $queryNameGenerator = new \ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator();

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, $entityClass, $operation, $context);

            if ($extension instanceof \ApiPlatform\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface && $extension->supportsResult($entityClass, $operation, $context)) {
                return $queryBuilder;
            }
        }

        return $queryBuilder;
    }
}
