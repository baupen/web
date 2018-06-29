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
use App\Api\Request\FoyerRequest;
use App\Api\Request\IssueRequest;
use App\Api\Response\Data\CraftsmanData;
use App\Api\Response\Data\Foyer\DeletedIssueData;
use App\Api\Response\Data\Foyer\NumberIssueData;
use App\Api\Response\Data\IssueData;
use App\Api\Transformer\Base\BaseEntityTransformer;
use App\Api\Transformer\Foyer\CraftsmanTransformer;
use App\Api\Transformer\Foyer\IssueTransformer;
use App\Api\Transformer\Foyer\NumberIssueTransformer;
use App\Controller\Api\Base\ApiController;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Issue;
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
    const INVALID_ISSUE = 'invalid issue';

    /**
     * @param Request $request
     * @param $issues
     * @param $entities
     * @param $errorResponse
     * @param $constructionSite
     *
     * @return bool
     */
    protected function parseFoyerRequest(Request $request, &$issues, &$entities, &$errorResponse, &$constructionSite)
    {
        /** @var FoyerRequest $parsedRequest */
        if (!parent::parseConstructionSiteRequest($request, FoyerRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return false;
        }

        //check at least one property set
        if (!is_array($parsedRequest->getIssues()) && !is_array($parsedRequest->getIssueIds())) {
            $errorResponse = $this->fail(self::REQUEST_VALIDATION_FAILED);
        }

        //retrieve all issues from the db
        /** @var Issue[] $requestedIssues */
        $requestedIssues = [];
        /** @var \App\Api\Entity\Foyer\Issue[] $issues */
        $issues = [];
        $issueRepo = $this->getDoctrine()->getRepository(Issue::class);
        if (is_array($parsedRequest->getIssueIds())) {
            $requestedIssues = $issueRepo->findBy(['id' => $parsedRequest->getIssueIds()]);
            $issues = array_flip($parsedRequest->getIssueIds());
        } else {
            foreach ($parsedRequest->getIssues() as $issue) {
                $issues[$issue->getId()] = $issue;
            }

            $requestedIssues = $issueRepo->findBy(['id' => array_keys($issues)]);
        }

        //ensure no issue from another contruction site
        foreach ($requestedIssues as $entity) {
            if ($entity->getMap()->getConstructionSite() !== $constructionSite) {
                $errorResponse = $this->fail(self::INVALID_CONSTRUCTION_SITE);

                return false;
            }
        }

        //sort entities
        $entities = [];
        foreach ($issues as $guid => $issue) {
            if (array_key_exists($guid, $requestedIssues)) {
                $entities[$guid] = $requestedIssues;
            }
        }

        return true;
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
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function issueListAction(Request $request, IssueTransformer $issueTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $constructionSiteRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $issues = $this->getDoctrine()->getRepository(Issue::class)->findBy(['registeredAt' => null], ['isMarked' => 'DESC', 'uploadedAt' => 'DESC']);

        //create response
        $data = new IssueData();
        $data->setIssues($issueTransformer->toApiMultiple($issues));

        return $this->success($data);
    }

    /**
     * @Route("/craftsman/list", name="api_foyer_craftsman_list", methods={"POST"})
     *
     * @param Request $request
     * @param CraftsmanTransformer $craftsmanTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function craftsmanListAction(Request $request, CraftsmanTransformer $craftsmanTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $constructionSiteRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $data = new CraftsmanData();
        $data->setCraftsmen($craftsmanTransformer->toApiMultiple($constructionSite->getCraftsmen()->toArray()));

        return $this->success($data);
    }

    /**
     * @Route("/issue/update", name="api_foyer_issue_update", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function issueUpdateAction(Request $request, IssueTransformer $issueTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var \App\Api\Entity\Foyer\Issue[] $issues */
        /** @var Issue[] $entities */
        if (!$this->parseFoyerRequest($request, $issues, $entities, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //write properties to issues
        foreach ($issues as $guid => $issue) {
            if (array_key_exists($guid, $entities)) {
                $entity = $entities[$guid];
                $issueTransformer->fromApi($issue, $entity);

                //get craftsman
                if ($issue->getCraftsmanId() !== null) {
                    $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->find($issue->getCraftsmanId());
                    if ($craftsman === null) {
                        return $this->fail(self::CRAFTSMAN_NOT_FOUND);
                    }
                    if ($craftsman->getConstructionSite() !== $constructionSite) {
                        return $this->fail(self::INVALID_CRAFTSMAN);
                    }
                    $entity->setCraftsman($craftsman);
                }
            }
        }

        $this->fastSave(...$entities);

        //create response
        $data = new IssueData();
        $data->setIssues($issueTransformer->toApiMultiple($issues));

        return $this->success($data);
    }

    /**
     * @Route("/issue/image", name="api_foyer_issue_image", methods={"POST"})
     *
     * @param Request $request
     * @param IssueTransformer $issueTransformer
     *
     * @return Response
     */
    public function issueImageAction(Request $request, IssueTransformer $issueTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var IssueRequest $issueRequest */
        if (!$this->parseConstructionSiteRequest($request, IssueRequest::class, $issueRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //get issue & ensure its on this construction site
        /** @var Issue $issue */
        $issue = $this->getDoctrine()->getRepository(Issue::class)->find($issueRequest->getIssueId());
        if ($issue === null) {
            return $this->fail(self::ISSUE_NOT_FOUND);
        }
        if ($issue->getMap()->getConstructionSite() !== $constructionSite) {
            return $this->fail(self::INVALID_ISSUE);
        }

        //check if file is here
        if ($request->files->count() !== 1) {
            return $this->fail(self::INCORRECT_NUMBER_OF_FILES);
        }

        /** @var UploadedFile $file */
        $file = $request->files->all()[0];

        //set new filename to avoid caching issues
        $issue->setImageFilename(Uuid::uuid4()->toString() . '.' . $file->guessExtension());

        //create folder & put file in there
        $targetFolder = $this->getParameter('PUBLIC_DIR') . '/' . dirname($issue->getImageFilePath());
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }
        if (!$file->move($targetFolder, $issue->getImageFilename())) {
            return $this->fail(self::FILE_UPLOAD_FAILED);
        }

        $this->fastSave($issue);

        //create response
        $data = new IssueData();
        $data->setIssues([$issueTransformer->toApi($issue)]);

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
        if (!$this->parseFoyerRequest($request, $issues, $entities, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $this->fastRemove(...$entities);

        $data = new DeletedIssueData();
        $data->setDeletedIssues($baseEntityTransformer->toApiMultiple($entities));

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
        if (!$this->parseFoyerRequest($request, $issues, $entities, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //set number & register event
        $canProceed = false;
        while (!$canProceed) {
            $highestNumber = $this->getDoctrine()->getRepository(Issue::class)->getHighestNumber($constructionSite);
            foreach ($entities as $entity) {
                $entity->setNumber($highestNumber++);
                $entity->setRegisteredAt(new \DateTime());
                $entity->setRegistrationBy($this->getUser());
            }

            //ensure no others have attempted to use these numbers
            $canProceed = $highestNumber === $this->getDoctrine()->getRepository(Issue::class)->getHighestNumber($constructionSite);
        }

        $this->fastSave(...$entities);

        //stats to client
        $data = new NumberIssueData();
        $data->setNumberIssues($numberIssueTransformer->toApiMultiple($entities));

        return $this->success($data);
    }
}
