<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use App\Api\Entity\Statistics\Overview;
use App\Api\Request\ConstructionSiteRequest;
use App\Api\Response\Data\Statistics\OverviewData;
use App\Controller\Api\Base\ApiController;
use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Entity\Issue;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/statistics")
 */
class StatisticsController extends ApiController
{
    /**
     * @Route("/issues/overview", name="api_statistics_overview", methods={"POST"})
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function issuesOverviewAction(Request $request)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $constructionSiteRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $issueRepo = $this->getDoctrine()->getRepository(Issue::class);

        //create response
        $overview = new Overview();

        //count new issues
        $filter = self::createRegisterFilter($constructionSite);
        $filter->filterByRegistrationStatus(false);
        $overview->setNewIssuesCount($issueRepo->countByFilter($filter));

        //count open issues
        $filter = self::createRegisterFilter($constructionSite);
        $filter->filterByReviewedStatus(false);
        $overview->setOpenIssuesCount($issueRepo->countByFilter($filter));

        //count marked issues
        $filter = self::createRegisterFilter($constructionSite);
        $filter->filterByIsMarked(true);
        $overview->setMarkedIssuesCount($issueRepo->countByFilter($filter));

        //count overdue issues
        $filter = self::createRegisterFilter($constructionSite);
        $filter->filterByResponseLimitEnd(new DateTime());
        $filter->filterByReviewedStatus(false);
        $overview->setOverdueIssuesCount($issueRepo->countByFilter($filter));

        //count overdue issues
        $filter = self::createRegisterFilter($constructionSite);
        $filter->filterByRespondedStatus(true);
        $filter->filterByReviewedStatus(false);
        $overview->setRespondedNotReviewedIssuesCount($issueRepo->countByFilter($filter));

        //return data
        $overviewData = new OverviewData();
        $overviewData->setOverview($overview);

        return $this->success($overviewData);
    }

    /**
     * @param ConstructionSite $constructionSite
     *
     * @return Filter
     */
    private static function createRegisterFilter(ConstructionSite $constructionSite)
    {
        $filter = new Filter();
        $filter->setConstructionSite($constructionSite);
        $filter->filterByRegistrationStatus(true);

        return $filter;
    }
}
