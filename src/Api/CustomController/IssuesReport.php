<?php

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

readonly class IssuesReport
{
    use TokenTrait;
    use FileResponseTrait;

    public function __construct(private ReportServiceInterface $reportService, private RequestStack $requestStack, private TokenStorageInterface $tokenStorage, private RouterInterface $router, private FilterServiceInterface $filterService)
    {
    }

    /**
     * @param Issue[] $data
     */
    public function __invoke(array $data): Response
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        $reportConfig = $currentRequest->query->all('report');
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
        if ($user = $this->tryGetConstructionManager($token)) {
            return $user->getName();
        }
        if ($craftsman = $this->tryGetCraftsman($token)) {
            return $craftsman->getContactName();
        }

        return null;
    }
}
