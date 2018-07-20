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
        $filter->setRegistrationStatus(true);
        $this->setFilter($filter, $constructionSite, $queryFilter);

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
     * @param ConstructionSite $constructionSite
     * @param array $query
     */
    private function setFilter(Filter $filter, ConstructionSite $constructionSite, $query)
    {
        $parameterBag = new ParameterBag($query);

        //parse input to array
        $toArray = function ($input) {
            return is_array($input) ? $input : [];
        };

        $craftsmanParameters = new ParameterBag($parameterBag->get('craftsman', []));
        if ($craftsmanParameters->getBoolean('enabled', false)) {
            $filter->setCraftsmen($toArray($craftsmanParameters->get('craftsmen', [])));
        }

        $mapParameters = new ParameterBag($parameterBag->get('map', []));
        if ($mapParameters->getBoolean('enabled', false)) {
            $filter->setMaps($toArray($mapParameters->get('maps', [])));
        }

        $tradeParameters = new ParameterBag($parameterBag->get('trade', []));
        if ($tradeParameters->getBoolean('enabled', false)) {
            $allowedTrades = $toArray($tradeParameters->get('trades', []));
            $craftsmanIds = [];
            foreach ($constructionSite->getCraftsmen() as $craftsman) {
                if (in_array($craftsman->getTrade(), $allowedTrades, true)) {
                    $craftsmanIds[] = $craftsman->getId();
                }
            }
            $filter->setCraftsmen($craftsmanIds);
        }

        $statusParameters = new ParameterBag($parameterBag->get('status', []));
        if ($statusParameters->getBoolean('enabled', false)) {
            $readParameters = new ParameterBag($statusParameters->get('read', []));
            if ($readParameters->getBoolean('active')) {
                $filter->setReadStatus($readParameters->getBoolean('value'));
            }

            //parse input to null or datetime
            $toDateTime = function ($input) {
                return $input === null || $input === '' ? null : new \DateTime($input);
            };

            $registeredParameters = new ParameterBag($statusParameters->get('registered', []));
            if ($registeredParameters->getBoolean('active')) {
                $filter->setRegistrationStart($toDateTime($registeredParameters->get('start', null)));
                $filter->setRegistrationEnd($toDateTime($registeredParameters->getBoolean('end', null)));
            }

            $respondedParameters = new ParameterBag($statusParameters->get('responded', []));
            if ($respondedParameters->getBoolean('active')) {
                $filter->setRespondedStatus($respondedParameters->getBoolean('value'));
                $filter->setRespondedStart($toDateTime($respondedParameters->get('start', null)));
                $filter->setRespondedEnd($toDateTime($respondedParameters->getBoolean('end', null)));
            }

            $reviewedParameters = new ParameterBag($statusParameters->get('reviewed', []));
            if ($reviewedParameters->getBoolean('active')) {
                $filter->setreviewedStatus($reviewedParameters->getBoolean('value'));
                $filter->setreviewedStart($toDateTime($reviewedParameters->get('start', null)));
                $filter->setreviewedEnd($toDateTime($reviewedParameters->getBoolean('end', null)));
            }
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
