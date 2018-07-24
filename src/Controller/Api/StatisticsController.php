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
use App\Api\Transformer\Register\IssueTransformer;
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
     * @param IssueTransformer $issueTransformer
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Response
     */
    public function issuesOverviewAction(Request $request, IssueTransformer $issueTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $constructionSiteRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $issueRepo = $this->getDoctrine()->getRepository(Issue::class);

        //prepare filter
        $filter = new Filter();
        $filter->setConstructionSite($constructionSite->getId());
        $filter->setRegistrationStatus(true);

        //create response
        $overview = new Overview();

        //count new issues
        $filter->setRegistrationStatus(false);
        $overview->setNewIssuesCount($issueRepo->filterCount($filter));
        $filter->setRegistrationStatus(null);

        //count open issues
        $filter->setReviewedStatus(false);
        $overview->setOpenIssuesCount($issueRepo->filterCount($filter));
        $filter->setReviewedStatus(null);

        //count marked issues
        $filter->setIsMarked(true);
        $overview->setMarkedIssuesCount($issueRepo->filterCount($filter));
        $filter->setIsMarked(null);

        //count overdue issues
        $filter->setLimitEnd(new \DateTime());
        $filter->setReviewedStatus(false);
        $overview->setOverdueIssuesCount($issueRepo->filterCount($filter));
        $filter->setLimitEnd(null);
        $filter->setReviewedStatus(null);

        //count overdue issues
        $filter->setRespondedStatus(true);
        $filter->setReviewedStatus(false);
        $overview->setRespondedNotReviewedIssuesCount($issueRepo->filterCount($filter));

        //return data
        $overviewData = new OverviewData();
        $overviewData->setOverview($overview);

        return $this->success($overviewData);
    }
}
