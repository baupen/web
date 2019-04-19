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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
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

        //prepare filter
        $filter = new Filter();
        $filter->setConstructionSite($constructionSite->getId());
        $filter->filterByRegistrationStatus(true);

        //create response
        $overview = new Overview();

        //count new issues
        $filter->filterByRegistrationStatus(false);
        $overview->setNewIssuesCount($issueRepo->filterCount($filter));
        $filter->filterByRegistrationStatus(null);

        //count open issues
        $filter->filterByReviewedStatus(false);
        $overview->setOpenIssuesCount($issueRepo->filterCount($filter));
        $filter->filterByReviewedStatus(null);

        //count marked issues
        $filter->filterByIsMarked(true);
        $overview->setMarkedIssuesCount($issueRepo->filterCount($filter));
        $filter->filterByIsMarked(null);

        //count overdue issues
        $filter->filterByResponseLimitEnd(new \DateTime());
        $filter->filterByReviewedStatus(false);
        $overview->setOverdueIssuesCount($issueRepo->filterCount($filter));
        $filter->filterByResponseLimitEnd(null);
        $filter->filterByReviewedStatus(null);

        //count overdue issues
        $filter->filterByRespondedStatus(true);
        $filter->filterByReviewedStatus(false);
        $overview->setRespondedNotReviewedIssuesCount($issueRepo->filterCount($filter));

        //return data
        $overviewData = new OverviewData();
        $overviewData->setOverview($overview);

        return $this->success($overviewData);
    }
}
