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
use App\Entity\Issue;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

class IssueReportDataProvider extends NoPaginationDataProvider
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    private const ALREADY_CALLED = 'ISSUE_REPORT_DATA_PROVIDER_ALREADY_CALLED';

    /**
     * IssueReportDataProvider constructor.
     */
    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack, iterable $collectionExtensions = [])
    {
        parent::__construct($managerRegistry, $collectionExtensions);

        $this->requestStack = $requestStack;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return Issue::class === $resourceClass && 'get_report' === $operationName;
    }

    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $currentRequest = $this->requestStack->getCurrentRequest();
        $currentRequest->attributes->set('filters', $context['filters']);

        $queryBuilder = $this->getCollectionQueryBuilerWithoutPagination($resourceClass, $operationName, $context);
        $queryBuilder->leftJoin($queryBuilder->getRootAliases()[0].'.image', 'i');
        $queryBuilder->addSelect('i');

        return $queryBuilder->getQuery()->getResult();
    }
}
