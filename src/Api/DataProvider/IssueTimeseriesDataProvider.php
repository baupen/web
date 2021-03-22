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
use App\Api\Entity\IssueSummaryWithDate;
use App\Entity\Issue;
use App\Service\Interfaces\AnalysisServiceInterface;
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
     * @var AnalysisServiceInterface
     */
    private $analysisService;

    public function __construct(SerializerInterface $serializer, ManagerRegistry $managerRegistry, AnalysisServiceInterface $analysisService, iterable $collectionExtensions = [])
    {
        parent::__construct($managerRegistry, $collectionExtensions);
        $this->serializer = $serializer;
        $this->analysisService = $analysisService;
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
        $issueAnalysisByTime = $this->analysisService->createIssueAnalysisByTime($rootAlias, $queryBuilder, $lastPeriodEnd, $stepSize, $stepCount);

        $summaries = [];
        foreach ($issueAnalysisByTime as $dateFormat => $issueAnalysis) {
            $summaries[] = IssueSummaryWithDate::createFromIssueAnalysisWithDate($issueAnalysis, $dateFormat);
        }

        $summaries = array_reverse($summaries); // want earliest (smallest) date first

        $json = $this->serializer->serialize($summaries, 'json');

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }
}
