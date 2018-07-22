<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\External\Report;

use App\Controller\Base\BaseDoctrineController;
use App\Controller\External\Traits\CraftsmanAuthenticationTrait;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Report\ReportElements;
use App\Service\Interfaces\ReportServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/c/{identifier}")
 */
class CraftsmanController extends BaseDoctrineController
{
    use CraftsmanAuthenticationTrait;

    /**
     * @Route("/{hash}", name="external_report_craftsman")
     *
     * @param $identifier
     * @param ReportServiceInterface $reportService
     *
     * @return Response
     */
    public function generateAction($identifier, ReportServiceInterface $reportService)
    {
        /** @var Craftsman $craftsman */
        if (!$this->parseIdentifierRequest($this->getDoctrine(), $identifier, $craftsman, $errorResponse)) {
            throw new NotFoundHttpException();
        }

        $filter = new Filter();
        $filter->setConstructionSite($craftsman->getConstructionSite()->getId());
        $filter->setCraftsmen([$craftsman->getId()]);
        $filter->setRespondedStatus(false);
        $filter->setRegistrationStatus(true);
        $filter->setReviewedStatus(false);

        $reportElements = ReportElements::forCraftsman();

        return $this->file(
            $reportService->generateReport($craftsman->getConstructionSite(), $filter, $craftsman->getName(), $reportElements),
            'report.pdf',
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }
}
