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
use App\Entity\ConstructionManager;
use App\Entity\Issue;
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Report\ReportElements;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    private const ALREADY_CALLED = 'ISSUE_REPORT_DATA_PROVIDER_ALREADY_CALLED';

    /**
     * IssueReportDataProvider constructor.
     */
    public function __construct(ContextAwareCollectionDataProviderInterface $decoratedCollectionDataProvider, ReportServiceInterface $reportService, TokenStorageInterface $tokenStorage, RequestStack $requestStack, ManagerRegistry $registry)
    {
        $this->decoratedCollectionDataProvider = $decoratedCollectionDataProvider;
        $this->reportService = $reportService;
        $this->tokenStorage = $tokenStorage;
        $this->request = $requestStack->getCurrentRequest();
        $this->registry = $registry;
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

        /** @var Paginator $collection */
        $collection = $this->decoratedCollectionDataProvider->getCollection($resourceClass, $operationName, $context);

        $reportElements = ReportElements::fromRequest($this->request->query->all());

        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof ConstructionManager) {
            $name = $user->getName();
        } else {
            throw new NotImplementedException('tokens not implemented');
        }

        $filePath = $this->reportService->generatePdfReport($collection, $context['filters'], $reportElements, $name);

        return new BinaryFileResponse($filePath);
    }
}
