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

use App\Api\Request\ConstructionSiteRequest;
use App\Api\Response\Data\CraftsmenData;
use App\Api\Response\Data\IssuesData;
use App\Api\Response\Data\MapsData;
use App\Api\Transformer\Register\CraftsmanTransformer;
use App\Api\Transformer\Register\IssueTransformer;
use App\Api\Transformer\Register\MapTransformer;
use App\Controller\Api\Base\ApiController;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Issue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/register")
 */
class RegisterController extends ApiController
{
    /**
     * @Route("/issue/list", name="api_register_issues_list", methods={"POST"})
     *
     * @param Request $request
     * @param IssueTransformer $issueTransformer
     *
     * @return Response
     */
    public function issueListAction(Request $request, IssueTransformer $issueTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $constructionSiteRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $filter = new Filter();
        $filter->setConstructionSite($constructionSite->getId());
        $filter->setRegistrationStatus(true);
        $issues = $this->getDoctrine()->getRepository(Issue::class)->filter($filter);

        //create response
        $data = new IssuesData();
        $data->setIssues($issueTransformer->toApiMultiple($issues));

        return $this->success($data);
    }

    /**
     * @Route("/craftsman/list", name="api_register_craftsman_list", methods={"POST"})
     *
     * @param Request $request
     * @param CraftsmanTransformer $craftsmanTransformer
     *
     * @return Response
     */
    public function craftsmanListAction(Request $request, CraftsmanTransformer $craftsmanTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $constructionSiteRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $data = new CraftsmenData();
        $data->setCraftsmen($craftsmanTransformer->toApiMultiple($constructionSite->getCraftsmen()->toArray()));

        return $this->success($data);
    }

    /**
     * @Route("/map/list", name="api_register_map_list", methods={"POST"})
     *
     * @param Request $request
     * @param MapTransformer $mapTransformer
     *
     * @return Response
     */
    public function mapAction(Request $request, MapTransformer $mapTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $constructionSiteRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $topLevelMaps = [];
        foreach ($constructionSite->getMaps() as $map) {
            if ($map->getParent() === null) {
                $topLevelMaps[] = $map;
            }
        }

        $data = new MapsData();
        $data->setMaps($mapTransformer->toApiMultiple($topLevelMaps));

        return $this->success($data);
    }
}
