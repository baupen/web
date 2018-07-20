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
use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Report\ReportElements;
use App\Service\Interfaces\ReportServiceInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
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
    /**
     * @Route("", name="report")
     *
     * @param Request $request
     * @param ReportServiceInterface $reportService
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
        $filter->setConstructionSite($constructionSite->getId());
        $this->setFilter($filter, $queryFilter);

        //create report elements
        $reportElements = new ReportElements();
        $this->setReportElements($reportElements, $queryReportElements);

        //generate report
        return $this->file(
            $reportService->generateReport($constructionSite, $filter, $this->getUser()->getName(), $reportElements),
            'report.pdf',
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }

    /**
     * @param Filter $filter
     * @param array $query
     */
    private function setFilter(Filter $filter, $query)
    {
        if ($query['craftsman']['enabled'] === true) {
            $filter->setCraftsmen($query['craftsman']['craftsmen']);
        }
    }

    /**
     * @param ReportElements $reportElements
     * @param array $query
     */
    private function setReportElements(ReportElements $reportElements, $query)
    {
        $parameterBag = new ParameterBag($query);
        $reportElements->setTableByCraftsman($parameterBag->getBoolean('tableByCraftsman', $reportElements->getTableByCraftsman()));
    }
}
