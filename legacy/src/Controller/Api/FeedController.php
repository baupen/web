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

use App\Api\Entity\Feed\Feed;
use App\Api\Request\ConstructionSiteRequest;
use App\Api\Response\Data\FeedData;
use App\Api\Transformer\Feed\FeedTransformer;
use App\Controller\Api\Base\ApiController;
use App\Entity\ConstructionSite;
use App\Feed\ResponseReceivedGeneratorIssue;
use App\Feed\VisitedWebpageGeneratorIssue;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/feed")
 */
class FeedController extends ApiController
{
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() +
            [
                ResponseReceivedGeneratorIssue::class => ResponseReceivedGeneratorIssue::class,
                VisitedWebpageGeneratorIssue::class => VisitedWebpageGeneratorIssue::class,
            ];
    }

    /**
     * @Route("/list", name="api_feed_list", methods={"POST"})
     *
     * @throws Exception
     *
     * @return Response
     */
    public function listAction(Request $request, FeedTransformer $feedTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $constructionSiteRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //create data object & return
        $feedData = new FeedData();
        $feedData->setFeed($feed);

        return $this->success($feedData);
    }
}
