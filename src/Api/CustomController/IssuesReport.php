<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\CustomController;

use App\Controller\Traits\FileResponseTrait;
use App\Entity\Issue;
use App\Security\TokenTrait;
use App\Service\Interfaces\FilterServiceInterface;
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Report\Pdf\ReportElements;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class IssuesReport
{
    use TokenTrait;
    use FileResponseTrait;

    private ReportServiceInterface $reportService;

    private TokenStorageInterface $tokenStorage;

    private RequestStack $requestStack;

    private RouterInterface $router;

    private FilterServiceInterface $filterService;

    /**
     * IssueReportDataProvider constructor.
     */
    public function __construct(ReportServiceInterface $reportService, RequestStack $requestStack, TokenStorageInterface $tokenStorage, RouterInterface $router, FilterServiceInterface $filterService)
    {
        $this->reportService = $reportService;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->filterService = $filterService;
    }

    /**
     * @param Issue[] $data
     */
    public function __invoke(array $data): Response
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        /** @var array|null $reportConfig */
        $reportConfig = $currentRequest->query->get('report');
        $reportElements = ReportElements::fromRequest($reportConfig);

        $author = $this->getAuthor($this->tokenStorage->getToken());

        $filters = $currentRequest->attributes->get('filters');
        $filter = $this->filterService->createFromQuery($filters);
        $filename = $this->reportService->generatePdfReport($data, $filter, $reportElements, $author);

        $path = $this->router->generate('public_download', ['filename' => $filename]);

        return new Response($path);
    }

    private function getAuthor(?TokenInterface $token): ?string
    {
        if (($user = $this->tryGetConstructionManager($token)) instanceof \App\Entity\ConstructionManager) {
            return $user->getName();
        } elseif (($craftsman = $this->tryGetCraftsman($token)) instanceof \App\Entity\Craftsman) {
            return $craftsman->getContactName();
        } else {
            return null;
        }
    }
}
