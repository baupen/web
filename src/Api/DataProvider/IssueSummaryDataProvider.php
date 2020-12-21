<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\DataProvider;

use App\Api\DataProvider\Base\NoPaginationDataProvider;
use App\Api\Entity\Summary;
use App\Entity\Issue;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class IssueSummaryDataProvider extends NoPaginationDataProvider
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer, ManagerRegistry $managerRegistry, iterable $collectionExtensions = [])
    {
        parent::__construct($managerRegistry, $collectionExtensions);
        $this->serializer = $serializer;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Issue::class === $resourceClass && 'get_summary' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $queryBuilder = $this->getCollectionQueryBuilerWithoutPagination($resourceClass, $operationName, $context);
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $summary = new Summary();
        $summary->setOpenCount($this->countOpenIssues($rootAlias, clone $queryBuilder));
        $summary->setResolvedCount($this->countResolvedIssues($rootAlias, clone $queryBuilder));
        $summary->setClosedCount($this->countClosedIssues($rootAlias, clone $queryBuilder));

        $json = $this->serializer->serialize($summary, 'json');

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    private function countOpenIssues(string $rootAlias, QueryBuilder $builder)
    {
        $builder->andWhere($rootAlias.'.registeredAt IS NOT NULL')
            ->andWhere($rootAlias.'.resolvedAt IS NULL')
            ->andWhere($rootAlias.'.closedAt IS NULL');

        return $this->countResult($rootAlias, $builder);
    }

    private function countResolvedIssues(string $rootAlias, QueryBuilder $builder)
    {
        $builder->andWhere($rootAlias.'.registeredAt IS NOT NULL')
            ->andWhere($rootAlias.'.resolvedAt IS NOT NULL')
            ->andWhere($rootAlias.'.closedAt IS NULL');

        return $this->countResult($rootAlias, $builder);
    }

    private function countClosedIssues(string $rootAlias, QueryBuilder $builder)
    {
        $builder->andWhere($rootAlias.'.registeredAt IS NOT NULL')
            ->andWhere($rootAlias.'.closedAt IS NOT NULL');

        return $this->countResult($rootAlias, $builder);
    }

    private function countResult(string $rootAlias, QueryBuilder $builder)
    {
        return $builder->select('count('.$rootAlias.')')
            ->getQuery()->getSingleScalarResult();
    }
}
