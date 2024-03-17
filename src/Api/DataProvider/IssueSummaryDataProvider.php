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

use App\Api\DataProvider\Base\NoPaginationDataProvider;
use App\Api\Entity\IssueSummary;
use App\Entity\Issue;
use App\Service\Interfaces\AnalysisServiceInterface;
use Doctrine\Persistence\ManagerRegistry;

class IssueSummaryDataProvider extends NoPaginationDataProvider
{
    /**
     * @var AnalysisServiceInterface
     */
    private $analysisService;

    public function __construct(ManagerRegistry $managerRegistry, AnalysisServiceInterface $analysisService, iterable $collectionExtensions = [])
    {
        parent::__construct($managerRegistry, $collectionExtensions);
        $this->analysisService = $analysisService;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return Issue::class === $resourceClass && 'get_summary' === $operationName;
    }

    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = [])
    {
        $queryBuilder = $this->getCollectionQueryBuilerWithoutPagination($resourceClass, $operationName, $context);
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $issueAnalysis = $this->analysisService->createIssueAnalysis($rootAlias, $queryBuilder);
        $summary = IssueSummary::createFromIssueAnalysis($issueAnalysis);

        return [$summary];
    }
}
