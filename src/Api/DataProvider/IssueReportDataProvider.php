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
use App\Controller\Traits\FileResponseTrait;
use App\Entity\Issue;
use App\Security\TokenTrait;
use App\Service\Interfaces\FilterServiceInterface;
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Report\ReportElements;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class IssueReportDataProvider extends NoPaginationDataProvider
{
    use TokenTrait;
    use FileResponseTrait;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var ReportServiceInterface
     */
    private $reportService;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FilterServiceInterface
     */
    private $filterService;

    private const ALREADY_CALLED = 'ISSUE_REPORT_DATA_PROVIDER_ALREADY_CALLED';

    /**
     * IssueReportDataProvider constructor.
     */
    public function __construct(ManagerRegistry $managerRegistry, ReportServiceInterface $reportService, RequestStack $requestStack, TokenStorageInterface $tokenStorage, RouterInterface $router, FilterServiceInterface $filterService, iterable $collectionExtensions = [])
    {
        parent::__construct($managerRegistry, $collectionExtensions);

        $this->managerRegistry = $managerRegistry;
        $this->reportService = $reportService;
        $this->tokenStorage = $tokenStorage;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->filterService = $filterService;
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

        $queryBuilder = $this->getCollectionQueryBuilerWithoutPagination($resourceClass, $operationName, $context);
        $queryBuilder->leftJoin($queryBuilder->getRootAliases()[0].'.image', 'i');
        $queryBuilder->addSelect('i');
        /** @var Issue[] $issues */
        $issues = $queryBuilder->getQuery()->getResult();

        /** @var array $reportConfig */
        $reportConfig = $this->request->query->get('report', []);
        $reportElements = ReportElements::fromRequest($reportConfig);

        $author = $this->getAuthor($this->tokenStorage->getToken());

        $filter = $this->filterService->createFromQuery($context['filters']);
        $filename = $this->reportService->generatePdfReport($issues, $filter, $reportElements, $author);

        return $this->router->generate('public_download', ['filename' => $filename]);
    }

    private function getAuthor(?TokenInterface $token): ?string
    {
        if ($user = $this->tryGetConstructionManager($token)) {
            return $user->getName();
        } elseif ($craftsman = $this->tryGetCraftsman($token)) {
            return $craftsman->getContactName();
        } else {
            return null;
        }
    }
}
