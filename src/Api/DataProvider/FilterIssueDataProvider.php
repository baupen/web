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

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\DenormalizedIdentifiersAwareItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Service\Interfaces\ReportServiceInterface;

class FilterIssueDataProvider implements DenormalizedIdentifiersAwareItemDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var ContextAwareCollectionDataProviderInterface
     */
    private $decoratedCollectionDataProvider;

    /**
     * @var ItemDataProviderInterface
     */
    private $decoratedItemDataProvider;

    /**
     * @var ReportServiceInterface
     */
    private $reportService;

    private const ALREADY_CALLED = 'FILTER_ISSUE_DATA_PROVIDER_ALREADY_CALLED';

    /**
     * IssueReportDataProvider constructor.
     */
    public function __construct(ContextAwareCollectionDataProviderInterface $decoratedCollectionDataProvider, ItemDataProviderInterface $decoratedItemDataProvider, ReportServiceInterface $reportService)
    {
        $this->decoratedCollectionDataProvider = $decoratedCollectionDataProvider;
        $this->decoratedItemDataProvider = $decoratedItemDataProvider;
        $this->reportService = $reportService;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return Filter::class === $resourceClass && 'get_issues' === $operationName;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $emulatedOperationName = 'get';
        $emulatedContext = array_merge($context, ['item_operation_name' => $emulatedOperationName]);
        /** @var Filter|null $filter */
        $filter = $this->decoratedItemDataProvider->getItem($resourceClass, $id, $emulatedOperationName, $emulatedContext);
        if (null === $filter) {
            return null;
        }

        $emulatedContext['collection_operation_name'] = 'get';
        $emulatedContext['resource_class'] = Issue::class;
        $emulatedContext = ['filters' => ['isMarked' => $filter->getIsMarked(), 'constructionSide' => $filter->getConstructionSite()]];

        $emulatedResourceClass = Issue::class;
        $emulatedOperationName = 'get';

        $collection = $this->decoratedCollectionDataProvider->getCollection($emulatedResourceClass, $emulatedOperationName, $emulatedContext);

        $filter->setIssues($collection);

        return $filter;
    }
}
