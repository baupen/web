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
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Issue;
use App\Service\Interfaces\ReportServiceInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IssueReportDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var ContextAwareCollectionDataProviderInterface
     */
    private $decoratedCollectionDataProvider;

    /**
     * @var ReportServiceInterface
     */
    private $reportService;

    private const ALREADY_CALLED = 'ISSUE_REPORT_DATA_PROVIDER_ALREADY_CALLED';

    /**
     * IssueReportDataProvider constructor.
     */
    public function __construct(ContextAwareCollectionDataProviderInterface $decoratedCollectionDataProvider, ReportServiceInterface $reportService)
    {
        $this->decoratedCollectionDataProvider = $decoratedCollectionDataProvider;
        $this->reportService = $reportService;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return Issue::class === $resourceClass && 'get_report' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;
        $context['pagination_enabled'] = false;

        // use data in $context["filters"] to find out restrictions
        // create report service which does not need more in interface
        $collection = $this->decoratedCollectionDataProvider->getCollection($resourceClass, $operationName, $context);

        return new BinaryFileResponse('file');
    }
}
