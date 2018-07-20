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
use App\Api\Response\Data\Register\ShareData;
use App\Api\Transformer\Register\CraftsmanTransformer;
use App\Api\Transformer\Register\IssueTransformer;
use App\Api\Transformer\Register\MapTransformer;
use App\Controller\Api\Base\ApiController;
use App\Controller\Traits\QueryParseTrait;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Issue;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/register")
 */
class RegisterController extends ApiController
{
    use QueryParseTrait;

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

    /**
     * @Route("/link/create", name="api_register_link_create", methods={"GET"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function linkCreateAction(Request $request)
    {
        $queryFilter = $request->query->get('filter', []);
        $queryLimit = $request->query->get('limit', []);

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

        //check if limit applies
        $linkParameters = new ParameterBag($queryLimit);
        if ($linkParameters->getBoolean('enabled')) {
            $limit = $linkParameters->get('limit', null);
            $dateLimit = $limit !== null && $limit !== '' ? new \DateTime($limit) : null;
            $filter->setShareAccessLimit($dateLimit);
        }

        //save
        $this->fastSave($filter);

        //send response
        $data = new ShareData();
        $data->setLink($this->generateUrl('external_share_filter', ['filter' => $filter->getId()], UrlGeneratorInterface::ABSOLUTE_URL));

        return $this->success($data);
    }
}
