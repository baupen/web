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
use App\Controller\External\Traits\FilterAuthenticationTrait;
use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Report\ReportElements;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/f/{identifier}")
 */
class FilterController extends BaseDoctrineController
{
    use FilterAuthenticationTrait;

    /**
     * @Route("/{hash}", name="external_report_filter")
     *
     * @param $identifier
     * @param ReportServiceInterface $reportService
     *
     * @return Response
     */
    public function generateAction($identifier, ReportServiceInterface $reportService)
    {
        /** @var Filter $filter */
        if (!$this->parseIdentifierRequest($this->getDoctrine(), $identifier, $filter)) {
            throw new NotFoundHttpException();
        }

        /** @var ConstructionSite|null $constructionSite */
        $constructionSite = $this->getDoctrine()->getRepository(ConstructionSite::class)->find($filter->getConstructionSite());
        if ($constructionSite === null) {
            throw new NotFoundHttpException();
        }

        $reportElements = ReportElements::forCraftsman();

        return $this->file(
            $reportService->generatePdfReport($constructionSite, $filter, $filter->getId(), $reportElements),
            'report.pdf',
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }
}
