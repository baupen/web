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
use App\Api\Request\IssueIdRequest;
use App\Api\Request\IssueIdsRequest;
use App\Api\Response\Data\CraftsmenData;
use App\Api\Response\Data\Foyer\DeletedIssueData;
use App\Api\Response\Data\Foyer\NumberIssueData;
use App\Api\Response\Data\IssueData;
use App\Api\Response\Data\IssuesData;
use App\Api\Transformer\Base\BaseEntityTransformer;
use App\Api\Transformer\Foyer\CraftsmanTransformer;
use App\Api\Transformer\Foyer\IssueTransformer;
use App\Api\Transformer\Foyer\NumberIssueTransformer;
use App\Api\Transformer\Foyer\UpdateIssueTransformer;
use App\Controller\Api\Base\ApiController;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/foyer")
 */
class FoyerController extends ApiController
{
    const INVALID_CONSTRUCTION_SITE = 'invalid construction site';
    const CRAFTSMAN_NOT_FOUND = 'craftsman not found';
    const INVALID_CRAFTSMAN = 'invalid craftsman';
    const FILE_UPLOAD_FAILED = 'file upload failed';
    const INCORRECT_NUMBER_OF_FILES = 'incorrect number of files';
    const ISSUE_NOT_FOUND = 'issue not found';

    /**
     * @param Request $request
     * @param $entities
     * @param $errorResponse
     * @param $constructionSite
     *
     * @return bool
     */
    private function parseIssuesRequest(Request $request, &$entities, &$errorResponse, &$constructionSite)
    {
        /** @var IssueIdsRequest $parsedRequest */
        /** @var ConstructionSite $constructionSite */
        if (!parent::parseConstructionSiteRequest($request, IssueIdsRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return false;
        }

        //retrieve all issues from the db
        $filter = new Filter();
        $filter->setRegistrationStatus(false);
        $filter->setConstructionSite($constructionSite->getId());
        $filter->setIssues($parsedRequest->getIssueIds());

        /** @var Issue[] $requestedIssues */
        /** @var \App\Api\Entity\Foyer\Issue[] $issues */
        $requestedIssues = $this->getDoctrine()->getRepository(Issue::class)->filter($filter);

        //order as requested
        $issues = array_flip($parsedRequest->getIssueIds());
        $this->orderEntities($requestedIssues, $issues, $entities);

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
    private function parseFoyerIssuesRequest(Request $request, &$issues, &$entities, &$errorResponse, &$constructionSite)
    {
        /** @var \App\Api\Request\Foyer\UpdateIssuesRequest $parsedRequest */
        /** @var ConstructionSite $constructionSite */
        if (!parent::parseConstructionSiteRequest($request, \App\Api\Request\Foyer\UpdateIssuesRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return false;
        }

        $issues = [];
        foreach ($parsedRequest->getUpdateIssues() as $arrayIssue) {
            $issue = $this->get('serializer')->deserialize(json_encode($arrayIssue), \App\Api\Entity\Foyer\UpdateIssue::class, 'json');
            $issues[$issue->getId()] = $issue;
        }

        //retrieve all issues from the db
        $filter = new Filter();
        $filter->setRegistrationStatus(false);
        $filter->setConstructionSite($constructionSite->getId());
        $filter->setIssues(array_keys($issues));

        $requestedIssues = $this->getDoctrine()->getRepository(Issue::class)->filter($filter);
        $this->orderEntities($requestedIssues, $issues, $entities);

        return true;
    }

    /**
     * @param Request $request
     * @param $entity
     * @param $errorResponse
     * @param $constructionSite
     *
     * @return bool
     */
    private function parseIssueRequest(Request $request, &$entity, &$errorResponse, &$constructionSite)
    {
        /** @var IssueIdRequest $parsedRequest */
        if (!parent::parseConstructionSiteRequest($request, IssueIdRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return false;
        }

        //get issue & ensure its on this construction site
        /** @var Issue $entity */
        $entity = $this->getDoctrine()->getRepository(Issue::class)->find($parsedRequest->getIssueId());
        if ($entity === null || $entity->getRegisteredAt() !== null) {
            $errorResponse = $this->fail(self::ISSUE_NOT_FOUND);

            return false;
        }
        if ($entity->getMap()->getConstructionSite() !== $constructionSite) {
            $errorResponse = $this->fail(self::INVALID_CONSTRUCTION_SITE);

            return false;
        }

        return true;
    }

    /**
     * @param Issue[] $requestedIssues
     * @param \App\Api\Entity\Foyer\Issue[] $issues
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
            if (array_key_exists($guid, $entityLookup)) {
                $entities[$guid] = $entityLookup[$guid];
            }
        }
    }

    /**
     * gives the appropriate error code the specified error message.
     *
     * @param string $message
     *
     * @return int
     */
    protected function errorMessageToStatusCode($message)
    {
        return parent::errorMessageToStatusCode($message);
    }

    /**
     * @Route("/issue/list", name="api_foyer_issues_list", methods={"POST"})
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
        $filter->setRegistrationStatus(false);
        $filter->setConstructionSite($constructionSite->getId());
        $issues = $this->getDoctrine()->getRepository(Issue::class)->filter($filter);

        //create response
        $data = new IssuesData();
        $data->setIssues($issueTransformer->toApiMultiple($issues));

        return $this->success($data);
    }

    /**
     * @Route("/craftsman/list", name="api_foyer_craftsman_list", methods={"POST"})
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
     * @Route("/issue/update", name="api_foyer_issue_update", methods={"POST"})
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
        /** @var \App\Api\Entity\Foyer\UpdateIssue[] $issues */
        /** @var Issue[] $entities */
        if (!$this->parseFoyerIssuesRequest($request, $issues, $entities, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //write properties to issues
        foreach ($issues as $guid => $issue) {
            if (array_key_exists($guid, $entities)) {
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
     * @Route("/issue/image", name="api_foyer_issue_image", methods={"POST"})
     *
     * @param Request $request
     * @param IssueTransformer $issueTransformer
     * @param PathServiceInterface $pathService
     * @param ImageServiceInterface $imageService
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function issueImageAction(Request $request, IssueTransformer $issueTransformer, PathServiceInterface $pathService, ImageServiceInterface $imageService)
    {
        /** @var ConstructionSite $constructionSite */
        /* @var IssueIdRequest $issueRequest */
        /** @var Issue $entity */
        if (!$this->parseIssueRequest($request, $entity, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //check if file is here
        if ($request->files->count() !== 1) {
            return $this->fail(self::INCORRECT_NUMBER_OF_FILES);
        }

        /** @var UploadedFile $file */
        $file = $request->files->getIterator()->current();

        //set new filename to avoid caching issues
        $targetFileName = Uuid::uuid4()->toString() . '.' . $file->guessExtension();

        //save file
        $issueImage = $this->uploadIssueImage($file, $entity, $targetFileName, $pathService, $imageService);
        if ($issueImage === null) {
            return $this->fail(self::FILE_UPLOAD_FAILED);
        }

        $issueImage->setIssue($entity);
        $entity->getImages()->add($issueImage);
        $entity->setImage($issueImage);
        $this->fastSave($entity);

        //create response
        $data = new IssueData();
        $data->setIssue($issueTransformer->toApi($entity));

        return $this->success($data);
    }

    /**
     * @Route("/issue/delete", name="api_foyer_issue_delete", methods={"POST"})
     *
     * @param Request $request
     * @param BaseEntityTransformer $baseEntityTransformer
     *
     * @return Response
     */
    public function issueDeleteAction(Request $request, BaseEntityTransformer $baseEntityTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseIssuesRequest($request, $entities, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $data = new DeletedIssueData();
        $data->setDeletedIssues($baseEntityTransformer->toApiMultiple($entities));

        $this->fastRemove(...array_values($entities));

        return $this->success($data);
    }

    /**
     * @Route("/issue/confirm", name="api_foyer_issue_confirm", methods={"POST"})
     *
     * @param Request $request
     * @param NumberIssueTransformer $numberIssueTransformer
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Response
     */
    public function issueConfirmAction(Request $request, NumberIssueTransformer $numberIssueTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var Issue[] $entities */
        if (!$this->parseIssuesRequest($request, $entities, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //set number & register event
        //note that this is unsafe to race conditions, and will crash because of db enforced constraints
        $highestNumber = $this->getDoctrine()->getRepository(Issue::class)->getHighestNumber($constructionSite);
        foreach ($entities as $entity) {
            $entity->setNumber(++$highestNumber);
            $entity->setRegisteredAt(new \DateTime());
            $entity->setRegistrationBy($this->getUser());
        }

        $this->fastSave(...array_values($entities));

        //stats to client
        $data = new NumberIssueData();
        $data->setNumberIssues($numberIssueTransformer->toApiMultiple($entities));

        return $this->success($data);
    }
}
