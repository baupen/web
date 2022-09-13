<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Encoder;

use App\Controller\Traits\FileResponseTrait;
use App\Entity\Issue;
use App\Security\TokenTrait;
use App\Service\Interfaces\FilterServiceInterface;
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Report\Pdf\ReportElements;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\NormalizationAwareInterface;

class ReportEncoder implements EncoderInterface, NormalizationAwareInterface
{
    use TokenTrait;
    use FileResponseTrait;

    /**
     * @var ReportServiceInterface
     */
    private $reportService;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FilterServiceInterface
     */
    private $filterService;

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

    public function encode($data, string $format, array $context = [])
    {
        /** @var Issue[] $data */
        $currentRequest = $this->requestStack->getCurrentRequest();
        /** @var array|null $reportConfig */
        $reportConfig = $currentRequest->query->get('report');
        $reportElements = ReportElements::fromRequest($reportConfig);

        $author = $this->getAuthor($this->tokenStorage->getToken());

        $filters = $currentRequest->attributes->get('filters');
        $filter = $this->filterService->createFromQuery($filters);
        $filename = $this->reportService->generatePdfReport($data, $filter, $reportElements, $author);

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

    public function supportsEncoding(string $format)
    {
        return 'issues_report' === $format;
    }
}
