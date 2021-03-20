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
use App\Entity\Issue;
use App\Service\Interfaces\IssueServiceInterface;
use DateInterval;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class IssueTimeseriesDataProvider extends NoPaginationDataProvider
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var IssueServiceInterface
     */
    private $issueService;

    public function __construct(SerializerInterface $serializer, ManagerRegistry $managerRegistry, IssueServiceInterface $issueService, iterable $collectionExtensions = [])
    {
        parent::__construct($managerRegistry, $collectionExtensions);
        $this->serializer = $serializer;
        $this->issueService = $issueService;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Issue::class === $resourceClass && 'get_timeseries' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $queryBuilder = $this->getCollectionQueryBuilerWithoutPagination($resourceClass, $operationName, $context);
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $lastPeriodEnd = new \DateTime('today');
        $stepSize = new DateInterval('P1D');
        $stepCount = 30;
        $summaries = $this->issueService->createTimeseries($rootAlias, $queryBuilder, $lastPeriodEnd, $stepSize, $stepCount);

        $json = $this->serializer->serialize($summaries, 'json');

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }
}
