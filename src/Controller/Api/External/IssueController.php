<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api\External;

use App\Api\External\Request\IssueActionRequest;
use App\Api\External\Request\IssueModifyRequest;
use App\Api\External\Response\Data\IssueData;
use App\Api\External\Transformer\IssueTransformer;
use App\Api\Response\Data\EmptyData;
use App\Controller\Api\External\Base\ExternalApiController;
use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Entity\Map;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/issue")
 *
 * @return Response
 */
class IssueController extends ExternalApiController
{
    const ISSUE_GUID_ALREADY_IN_USE = 'guid already in use';

    const ISSUE_NOT_FOUND = 'issue was not found';
    const ISSUE_ACCESS_DENIED = 'issue access not allowed';
    const ISSUE_ACTION_NOT_ALLOWED = 'this action can not be executed on the entity';

    const MAP_NOT_FOUND = 'map was not found';
    const MAP_ACCESS_DENIED = 'map access not allowed';

    const CRAFTSMAN_NOT_FOUND = 'craftsman was not found';
    const CRAFTSMAN_ACCESS_DENIED = 'craftsman access not allowed';

    const MAP_CRAFTSMAN_NOT_ON_SAME_CONSTRUCTION_SITE = 'the craftsman does not work on the same construction site as the assigned map';

    const ISSUE_FILE_UPLOAD_FAILED = 'the uploaded file could not be processes';
    const ISSUE_NO_FILE_TO_UPLOAD = 'no file could be found in the request, but one was expected';
    const ISSUE_NO_FILE_UPLOAD_EXPECTED = 'a file was uploaded, but not specified in the issue';

    /**
     * gives the appropriate error code the specified error message.
     *
     * @param string $message
     *
     * @return int
     */
    protected function errorMessageToStatusCode($message)
    {
        switch ($message) {
            case static::ISSUE_GUID_ALREADY_IN_USE:
                return 200;
            case static::ISSUE_NOT_FOUND:
                return 201;
            case static::ISSUE_ACTION_NOT_ALLOWED:
                return 203;
        }

        return parent::errorMessageToStatusCode($message);
    }

    /**
     * @Route("/create", name="api_external_issue_create", methods={"POST"})
     *
     * @param Request $request
     * @param IssueTransformer $issueTransformer
     *
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return Response
     */
    public function issueCreateAction(Request $request, IssueTransformer $issueTransformer)
    {
        return $this->processIssueModifyRequest($request, $issueTransformer, 'create');
    }

    /**
     * @Route("/update", name="api_external_issue_update", methods={"POST"})
     *
     * @param Request $request
     * @param IssueTransformer $issueTransformer
     *
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return Response
     */
    public function issueUpdateAction(Request $request, IssueTransformer $issueTransformer)
    {
        return $this->processIssueModifyRequest($request, $issueTransformer, 'update');
    }

    /**
     * @param Request $request
     * @param IssueTransformer $issueTransformer
     * @param $mode
     *
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return JsonResponse|Response
     */
    private function processIssueModifyRequest(Request $request, IssueTransformer $issueTransformer, $mode)
    {
        /** @var IssueModifyRequest $issueModifyRequest */
        /** @var ConstructionManager $constructionManager */
        if (!$this->parseAuthenticatedRequest($request, IssueModifyRequest::class, $issueModifyRequest, $errorResponse, $constructionManager)) {
            return $errorResponse;
        }

        $newImageExpected = $issueModifyRequest->getIssue()->getImageFilename() !== null;
        if ($mode === 'create') {
            //ensure GUID not in use already
            $existing = $this->getDoctrine()->getRepository(Issue::class)->find($issueModifyRequest->getIssue()->getMeta()->getId());
            if ($existing !== null) {
                return $this->fail(static::ISSUE_GUID_ALREADY_IN_USE);
            }
            $entity = new Issue();
        } elseif ($mode === 'update') {
            //ensure issue exists
            $existing = $this->getDoctrine()->getRepository(Issue::class)->find($issueModifyRequest->getIssue()->getMeta()->getId());
            if ($existing === null) {
                return $this->fail(static::ISSUE_NOT_FOUND);
            }
            $entity = $existing;
            $newImageExpected &= $issueModifyRequest->getIssue()->getImageFilename() !== $existing->getImageFilename();
        } else {
            throw new \InvalidArgumentException('mode must be create or update');
        }

        //transform to entity
        $issue = $issueTransformer->fromApi($issueModifyRequest->getIssue(), $entity);
        $issue->setUploadBy($constructionManager);
        $issue->setUploadedAt(new \DateTime());

        //get map & check access
        /** @var Map $map */
        $map = $this->getDoctrine()->getRepository(Map::class)->findOneBy(['id' => $issueModifyRequest->getIssue()->getMap()]);
        if ($map === null) {
            return $this->fail(static::MAP_NOT_FOUND);
        }
        if (!$map->getConstructionSite()->getConstructionManagers()->contains($constructionManager)) {
            return $this->fail(static::MAP_ACCESS_DENIED);
        }
        $issue->setMap($map);

        //get craftsmen & check access
        if ($issueModifyRequest->getIssue()->getCraftsman() !== null) {
            /** @var Craftsman $craftsman */
            $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->findOneBy(['id' => $issueModifyRequest->getIssue()->getCraftsman()]);
            if ($craftsman === null) {
                return $this->fail(static::CRAFTSMAN_NOT_FOUND);
            }
            if (!$craftsman->getConstructionSite()->getConstructionManagers()->contains($constructionManager)) {
                return $this->fail(static::CRAFTSMAN_ACCESS_DENIED);
            }
            $issue->setCraftsman($craftsman);
        }

        //ensure craftsman & map on same construction site
        if ($issue->getMap() !== null && $issue->getCraftsman() !== null &&
            $issue->getMap()->getConstructionSite()->getId() !== $issue->getCraftsman()->getConstructionSite()->getId()) {
            return $this->fail(static::MAP_CRAFTSMAN_NOT_ON_SAME_CONSTRUCTION_SITE);
        }

        //ensure correct number of files
        if ($newImageExpected && count($request->files->all()) !== 1) {
            return $this->fail(static::ISSUE_NO_FILE_TO_UPLOAD);
        }
        if (!$newImageExpected && count($request->files->all()) !== 0) {
            return $this->fail(static::ISSUE_NO_FILE_UPLOAD_EXPECTED);
        }

        //handle file upload
        foreach ($request->files->all() as $file) {
            /** @var UploadedFile $file */
            $targetFolder = $this->getParameter('PUBLIC_DIR') . '/' . dirname($issue->getImageFilePath());
            if (!file_exists($targetFolder)) {
                mkdir($targetFolder, 0777, true);
            }
            if (!$file->move($targetFolder, $issue->getImageFilename())) {
                return $this->fail(static::ISSUE_FILE_UPLOAD_FAILED);
            }
        }

        //if create, need to enforce correct GUID
        if ($mode === 'create') {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            //deactivate guid generator so we can use the one the client has sent us
            $metadata = $em->getClassMetadata(get_class($issue));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
            $issue->setId($issueModifyRequest->getIssue()->getMeta()->getId());

            //persist to db
            $em->persist($issue);
            $em->flush();
        } else {
            $this->fastSave($issue);
        }

        //construct answer
        return $this->success(new IssueData($issueTransformer->toApi($issue)));
    }

    /**
     * @Route("/delete", name="api_external_issue_delete", methods={"POST"})
     *
     * @param Request $request
     * @param IssueTransformer $issueTransformer
     *
     * @throws ORMException
     *
     * @return Response
     */
    public function issueDeleteAction(Request $request, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest(
            $request, $issueTransformer, function ($issue) {
                /** @var Issue $issue */
                if ($issue->getRegisteredAt() === null) {
                    $this->fastRemove($issue);

                    return $this->success(new EmptyData());
                }

                return $this->fail(static::ISSUE_ACTION_NOT_ALLOWED);
            }
        );
    }

    /**
     * @Route("/mark", name="api_external_issue_mark", methods={"POST"})
     *
     * @param Request $request
     * @param IssueTransformer $issueTransformer
     *
     * @throws ORMException
     *
     * @return Response
     */
    public function issueMarkAction(Request $request, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest(
            $request, $issueTransformer, function ($issue) {
                /* @var Issue $issue */
                $issue->setIsMarked(!$issue->getIsMarked());
                $this->fastSave($issue);

                return true;
            }
        );
    }

    /**
     * @Route("/review", name="api_external_issue_review", methods={"POST"})
     *
     * @param Request $request
     * @param IssueTransformer $issueTransformer
     *
     * @throws ORMException
     *
     * @return Response
     */
    public function issueReviewAction(Request $request, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest(
            $request, $issueTransformer, function ($issue, $constructionManager) {
                /** @var Issue $issue */
                /* @var ConstructionManager $constructionManager */
                if ($issue->getRegisteredAt() !== null && $issue->getReviewedAt() === null) {
                    $issue->setReviewedAt(new \DateTime());
                    $issue->setReviewBy($constructionManager);
                    $this->fastSave($issue);

                    return true;
                }

                return $this->fail(static::ISSUE_ACTION_NOT_ALLOWED);
            }
        );
    }

    /**
     * @Route("/revert", name="api_external_issue_revert", methods={"POST"})
     *
     * @param Request $request
     * @param IssueTransformer $issueTransformer
     *
     * @throws ORMException
     *
     * @return Response
     */
    public function issueRevertAction(Request $request, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest(
            $request, $issueTransformer, function ($issue) {
                /** @var Issue $issue */
                if ($issue->getRegisteredAt() !== null) {
                    if ($issue->getReviewedAt() !== null) {
                        $issue->setReviewedAt(null);
                        $issue->setReviewBy(null);
                    } elseif ($issue->getRespondedAt() !== null) {
                        $issue->setRespondedAt(null);
                        $issue->setResponseBy(null);
                    } else {
                        return $this->fail(static::ISSUE_ACTION_NOT_ALLOWED);
                    }
                    $this->fastSave($issue);

                    return true;
                }

                return $this->fail(static::ISSUE_ACTION_NOT_ALLOWED);
            }
        );
    }

    /**
     * @param Request $request
     * @param IssueTransformer $issueTransformer
     * @param $action
     *
     * @throws ORMException
     *
     * @return JsonResponse|Response
     */
    private function processIssueActionRequest(Request $request, IssueTransformer $issueTransformer, $action)
    {
        /** @var IssueActionRequest $issueActionRequest */
        /** @var ConstructionManager $constructionManager */
        if (!$this->parseAuthenticatedRequest($request, IssueActionRequest::class, $issueActionRequest, $errorResponse, $constructionManager)) {
            return $errorResponse;
        }

        //get issue
        /** @var Issue $issue */
        $issue = $this->getDoctrine()->getRepository(Issue::class)->find($issueActionRequest->getIssueID());
        if ($issue === null) {
            return $this->fail(static::ISSUE_NOT_FOUND);
        }
        //ensure we are allowed to access this issue
        if (!$issue->getMap()->getConstructionSite()->getConstructionManagers()->contains($constructionManager)) {
            return $this->fail(static::ISSUE_ACCESS_DENIED);
        }

        //execute action
        $response = $action($issue, $constructionManager);
        if ($response instanceof Response) {
            return $response;
        }

        //construct answer
        return $this->success(new IssueData($issueTransformer->toApi($issue)));
    }
}
