<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\External;

use App\Controller\Base\BaseDoctrineController;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Service\Interfaces\ReportServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/report")
 */
class ReportController extends BaseDoctrineController
{
    /**
     * @Route("/c/{identifier}/{hash}", name="external_report_craftsman")
     *
     * @param $identifier
     * @param $hash
     * @param ReportServiceInterface $reportService
     *
     * @return Response
     */
    public function craftsmanAction($identifier, $hash, ReportServiceInterface $reportService)
    {
        /** @var Craftsman $craftsman */
        $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->findOneBy(['emailIdentifier' => $identifier]);
        if ($craftsman === null) {
            throw new NotFoundHttpException();
        }

        $filter = new Filter();
        $filter->setConstructionSite($craftsman->getConstructionSite()->getId());
        $filter->setCraftsmen([$craftsman->getId()]);
        $filter->setRespondedStatus(false);
        $filter->setRegistrationStatus(true);
        $filter->setReviewedStatus(false);

        return $this->file($reportService->generateReport($craftsman->getConstructionSite(), $filter));
    }
}
