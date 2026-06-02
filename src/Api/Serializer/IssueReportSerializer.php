<?php

namespace App\Api\Serializer;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Security\TokenTrait;
use App\Service\Interfaces\FilterServiceInterface;
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Report\Pdf\ReportElements;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

#[AsDecorator(decorates: 'api_platform.state_processor.serialize')]
readonly class IssueReportSerializer implements ProcessorInterface
{
    use TokenTrait;

    public function __construct(
        #[AutowireDecorated]
        private ProcessorInterface $decorated,
        private ReportServiceInterface $reportService,
        private RequestStack $requestStack,
        private TokenStorageInterface $tokenStorage,
        private RouterInterface $router,
        private FilterServiceInterface $filterService
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        /** @var HttpOperation $operation */
        if ($operation->getUriTemplate() !== '/issues/report') {
            return $this->decorated->process($data, $operation, $uriVariables, $context);
        }

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
