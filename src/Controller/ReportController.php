<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseDoctrineController;
use App\Controller\Traits\QueryParseTrait;
use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Report\ReportElements;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/report")
 */
class ReportController extends BaseDoctrineController
{
    use QueryParseTrait;

    /**
     * @Route("", name="report")
     *
     * @param Request $request
     * @param ReportServiceInterface $reportService
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function reportAction(Request $request, ReportServiceInterface $reportService)
    {
        $queryFilter = $request->query->get('filter', []);
        $queryReportElements = $request->query->get('reportElements', []);

        //get construction site
        if (!isset($queryFilter['constructionSiteId'])) {
            throw new NotFoundHttpException();
        }
        $constructionSite = $this->getDoctrine()->getRepository(ConstructionSite::class)->find($queryFilter['constructionSiteId']);
        if ($constructionSite === null || !$this->getUser()->getConstructionSites()->contains($constructionSite)) {
            throw new NotFoundHttpException();
        }

        //create filter
        $filter = new Filter();
        $this->setFilterProperties($filter, $constructionSite, $queryFilter);
        $filter->setRegistrationStatus(true);
        $filter->setConstructionSite($constructionSite->getId());

        //create report elements
        $reportElements = new ReportElements();
        $this->setReportElements($reportElements, $queryReportElements);

        //generate report
        return $this->file(
            $reportService->generatePdfReport($constructionSite, $filter, $this->getUser()->getName(), $reportElements),
            'report.pdf',
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }
}
