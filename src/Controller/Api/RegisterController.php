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
use App\Api\Request\IssueIdsRequest;
use App\Api\Request\Register\SetStatusRequest;
use App\Api\Request\Register\UpdateIssuesRequest;
use App\Api\Response\Data\CraftsmenData;
use App\Api\Response\Data\IssuesData;
use App\Api\Response\Data\MapsData;
use App\Api\Response\Data\Register\ShareData;
use App\Api\Transformer\Register\CraftsmanTransformer;
use App\Api\Transformer\Register\IssueTransformer;
use App\Api\Transformer\Register\MapTransformer;
use App\Api\Transformer\Register\UpdateIssueTransformer;
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

    const CRAFTSMAN_NOT_FOUND = 'craftsman not found';
    const INVALID_CRAFTSMAN = 'invalid craftsman';
    const EMPTY_CONDITIONS_NOT_ALLOWED = 'empty conditions not allowed';

    /**
     * @param Request $request
     * @param $entities
     * @param $errorResponse
     * @param $constructionSite
     * @param $parsedRequest
     * @param string $class
     *
     * @return bool
     */
    private function parseIssuesRequest(Request $request, &$entities, &$errorResponse, &$constructionSite, &$parsedRequest, $class = IssueIdsRequest::class)
    {
        /** @var IssueIdsRequest $parsedRequest */
        /** @var ConstructionSite $constructionSite */
        if (!parent::parseConstructionSiteRequest($request, $class, $parsedRequest, $errorResponse, $constructionSite)) {
            return false;
        }

        //retrieve all issues from the db
        $filter = new Filter();
        $filter->filterByRegistrationStatus(true);
        $filter->setConstructionSite($constructionSite);
        $filter->filterByIssues($parsedRequest->getIssueIds());

        /** @var Issue[] $requestedIssues */
        /** @var \App\Api\Entity\Foyer\Issue[] $issues */
        $requestedIssues = $this->getDoctrine()->getRepository(Issue::class)->findByFilter($filter);
        $issues = array_flip($parsedRequest->getIssueIds());

        $this->orderEntities($requestedIssues, $issues, $entities);

        return true;
    }

    /**
     * @param Request $request
     * @param $entities
     * @param $errorResponse
     * @param $constructionSite
     * @param $parsedRequest
     *
     * @return bool
     */
    private function parseSetStatusRequest(Request $request, &$entities, &$errorResponse, &$constructionSite, &$parsedRequest)
    {
        /** @var IssueIdsRequest $parsedRequest */
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseIssuesRequest($request, $entities, $errorResponse, $constructionSite, $parsedRequest, SetStatusRequest::class)) {
            return false;
        }

        return true;
    }

    /**
     * @param Request $request
     * @param $issues
     * @param $entities
     * @param $errorResponse
     * @param $constructionSite
     *
     * @return bool
     */
    private function parseRegisterIssuesRequest(Request $request, &$issues, &$entities, &$errorResponse, &$constructionSite)
    {
        /** @var UpdateIssuesRequest $parsedRequest */
        /** @var ConstructionSite $constructionSite */
        if (!parent::parseConstructionSiteRequest($request, UpdateIssuesRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return false;
        }

        $issues = [];
        foreach ($parsedRequest->getUpdateIssues() as $arrayIssue) {
            $issue = $this->get('serializer')->deserialize(json_encode($arrayIssue), \App\Api\Entity\Register\UpdateIssue::class, 'json');
            $issues[$issue->getId()] = $issue;
        }

        //retrieve all issues from the db
        $filter = new Filter();
        $filter->filterByRegistrationStatus(true);
        $filter->setConstructionSite($constructionSite);
        $filter->filterByIssues(array_keys($issues));

        $requestedIssues = $this->getDoctrine()->getRepository(Issue::class)->findByFilter($filter);
        $this->orderEntities($requestedIssues, $issues, $entities);

        return true;
    }

    /**
     * @param Issue[] $requestedIssues
     * @param \App\Api\Entity\Register\UpdateIssue[] $issues
     * @param Issue[] $entities
     */
    private function orderEntities($requestedIssues, $issues, &$entities)
    {
        //build lookup from database entities
        $entityLookup = [];
        foreach ($requestedIssues as $entity) {
            $entityLookup[$entity->getId()] = $entity;
        }

        //sort entities
        $entities = [];
        foreach ($issues as $guid => $issue) {
            if (\array_key_exists($guid, $entityLookup)) {
                $entities[$guid] = $entityLookup[$guid];
            }
        }
    }

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
        $filter->setConstructionSite($constructionSite);
        $filter->filterByRegistrationStatus(true);
        $issues = $this->getDoctrine()->getRepository(Issue::class)->findByFilter($filter);

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
     * @throws \Exception
     * @throws \Exception
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

        /** @var ConstructionSite $constructionSite */
        $constructionSite = $this->getDoctrine()->getRepository(ConstructionSite::class)->find($queryFilter['constructionSiteId']);
        if ($constructionSite === null || !$this->getUser()->getConstructionSites()->contains($constructionSite)) {
            throw new NotFoundHttpException();
        }

        //create filter
        $filter = new Filter();
        $this->setFilterProperties($filter, $queryFilter);
        $filter->filterByRegistrationStatus(true);
        $filter->setConstructionSite($constructionSite);
        $filter->setPublicAccessIdentifier();

        //check if limit applies
        $linkParameters = new ParameterBag($queryLimit);
        if ($linkParameters->getBoolean('enabled')) {
            $limit = $linkParameters->get('limit', null);
            $dateLimit = $limit !== null && $limit !== '' ? new \DateTime($limit) : null;
            $filter->setAccessAllowedUntil($dateLimit);
        }

        //save
        $this->fastSave($filter);

        //send response
        $data = new ShareData();
        $data->setLink($this->generateUrl('external_share_filter', ['identifier' => $filter->getPublicAccessIdentifier()], UrlGeneratorInterface::ABSOLUTE_URL));

        return $this->success($data);
    }

    /**
     * @Route("/issue/update", name="api_register_issue_update", methods={"POST"})
     *
     * @param Request $request
     * @param UpdateIssueTransformer $updateIssueTransformer
     * @param IssueTransformer $issueTransformer
     *
     * @return Response
     */
    public function issueUpdateAction(Request $request, UpdateIssueTransformer $updateIssueTransformer, IssueTransformer $issueTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var \App\Api\Entity\Register\UpdateIssue[] $issues */
        /** @var Issue[] $entities */
        if (!$this->parseRegisterIssuesRequest($request, $issues, $entities, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //write properties to issues
        foreach ($issues as $guid => $issue) {
            if (\array_key_exists($guid, $entities)) {
                $entity = $entities[$guid];
                $res = $updateIssueTransformer->fromApi($issue, $entity, function ($craftsman) use ($constructionSite) {
                    /** @var Craftsman $craftsman */
                    if ($craftsman === null) {
                        return $this->fail(self::CRAFTSMAN_NOT_FOUND);
                    }
                    if ($craftsman->getConstructionSite() !== $constructionSite) {
                        return $this->fail(self::INVALID_CRAFTSMAN);
                    }

                    return true;
                });
                if ($res !== true) {
                    /* @var Response $res */
                    return $res;
                }
            }
        }

        $this->fastSave(...array_values($entities));

        //create response
        $data = new IssuesData();
        $data->setIssues($issueTransformer->toApiMultiple($entities));

        return $this->success($data);
    }

    /**
     * @Route("/issue/status", name="api_register_issue_status", methods={"POST"})
     *
     * @param Request $request
     * @param IssueTransformer $issueTransformer
     *
     * @throws \Exception
     * @throws \Exception
     * @throws \Exception
     *
     * @return Response
     */
    public function issueStatusAction(Request $request, IssueTransformer $issueTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var Issue[] $entities */
        /** @var SetStatusRequest $parsedRequest */
        if (!$this->parseSetStatusRequest($request, $entities, $errorResponse, $constructionSite, $parsedRequest)) {
            return $errorResponse;
        }

        //correct responded status
        if ($parsedRequest->isRespondedStatusSet()) {
            foreach ($entities as $entity) {
                if ($entity->getRespondedAt() === null) {
                    $entity->setRespondedAt(new \DateTime());
                    $entity->setResponseBy($entity->getCraftsman());
                }
            }
        } else {
            foreach ($entities as $entity) {
                if ($entity->getRespondedAt() !== null) {
                    $entity->setRespondedAt(null);
                    $entity->setResponseBy(null);
                }
            }
        }

        //correct reviewed status
        if ($parsedRequest->isReviewedStatusSet()) {
            foreach ($entities as $entity) {
                if ($entity->getReviewedAt() === null) {
                    $entity->setReviewedAt(new \DateTime());
                    $entity->setReviewBy($this->getUser());
                }
            }
        } else {
            foreach ($entities as $entity) {
                if ($entity->getReviewedAt() !== null) {
                    $entity->setReviewedAt(null);
                    $entity->setReviewBy(null);
                }
            }
        }

        $this->fastSave(...array_values($entities));

        //create response
        $data = new IssuesData();
        $data->setIssues($issueTransformer->toApiMultiple($entities));

        return $this->success($data);
    }
}
