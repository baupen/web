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

use App\Api\Request\IssueActionRequest;
use App\Api\Request\IssueModifyRequest;
use App\Api\Response\Data\EmptyData;
use App\Api\Response\Data\IssueData;
use App\Api\Transformer\IssueTransformer;
use App\Controller\Api\Base\BaseApiController;
use App\Entity\AuthenticationToken;
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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/issue")
 *
 * @return Response
 */
class IssueController extends BaseApiController
{
    /**
     * @Route("/create", name="api_issue_create")
     *
     * @param Request             $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface  $validator
     * @param IssueTransformer    $issueTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function issueCreateAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueModifyRequest($request, $serializer, $validator, $issueTransformer, 'create');
    }

    /**
     * @Route("/update", name="api_issue_update")
     *
     * @param Request             $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface  $validator
     * @param IssueTransformer    $issueTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function issueUpdateAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueModifyRequest($request, $serializer, $validator, $issueTransformer, 'update');
    }

    /**
     * @param Request             $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface  $validator
     * @param IssueTransformer    $issueTransformer
     * @param $mode
     *
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return JsonResponse|Response
     */
    private function processIssueModifyRequest(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer, $mode)
    {
        //check if empty request, ensure multipart correctly handled
        $content = $request->request->get('message');
        if (!$content) {
            $content = $request->getContent();
        }
        if (!($content)) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var IssueModifyRequest $issueModifyRequest */
        $issueModifyRequest = $serializer->deserialize($content, IssueModifyRequest::class, 'json');

        // check all properties defined
        $errors = $validator->validate($issueModifyRequest);
        if (count($errors) > 0) {
            return $this->fail(static::REQUEST_VALIDATION_FAILED);
        }

        //check auth token
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(AuthenticationToken::class)->getConstructionManager($issueModifyRequest);
        if (null === $constructionManager) {
            return $this->fail(static::AUTHENTICATION_TOKEN_INVALID);
        }

        $newImageExpected = null !== $issueModifyRequest->getIssue()->getImageFilename();
        if ('create' === $mode) {
            //ensure GUID not in use already
            $existing = $this->getDoctrine()->getRepository(Issue::class)->find($issueModifyRequest->getIssue()->getMeta()->getId());
            if (null !== $existing) {
                return $this->fail(static::ISSUE_GUID_ALREADY_IN_USE);
            }
            $entity = new Issue();
        } elseif ('update' === $mode) {
            //ensure issue exists
            $existing = $this->getDoctrine()->getRepository(Issue::class)->find($issueModifyRequest->getIssue()->getMeta()->getId());
            if (null === $existing) {
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
        if (null === $map) {
            return $this->fail(static::MAP_NOT_FOUND);
        }
        if (!$map->getConstructionSite()->getConstructionManagers()->contains($constructionManager)) {
            return $this->fail(static::MAP_ACCESS_DENIED);
        }
        $issue->setMap($map);

        //get craftsmen & check access
        if (null !== $issueModifyRequest->getIssue()->getCraftsman()) {
            /** @var Craftsman $craftsman */
            $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->findOneBy(['id' => $issueModifyRequest->getIssue()->getCraftsman()]);
            if (null === $craftsman) {
                return $this->fail(static::CRAFTSMAN_NOT_FOUND);
            }
            if (!$craftsman->getConstructionSite()->getConstructionManagers()->contains($constructionManager)) {
                return $this->fail(static::CRAFTSMAN_ACCESS_DENIED);
            }
            $issue->setCraftsman($craftsman);
        }

        //ensure craftsman & map on same construction site
        if (null !== $issue->getMap() && null !== $issue->getCraftsman() &&
            $issue->getMap()->getConstructionSite()->getId() !== $issue->getCraftsman()->getConstructionSite()->getId()) {
            return $this->fail(static::MAP_CRAFTSMAN_NOT_ON_SAME_CONSTRUCTION_SITE);
        }

        //ensure correct number of files
        if ($newImageExpected && 1 !== count($request->files->all())) {
            return $this->fail(static::ISSUE_NO_FILE_TO_UPLOAD);
        }
        if (!$newImageExpected && 0 !== count($request->files->all())) {
            return $this->fail(static::ISSUE_NO_FILE_UPLOAD_EXPECTED);
        }

        //handle file upload
        foreach ($request->files->all() as $key => $file) {
            /** @var UploadedFile $file */
            $targetFolder = $this->getParameter('PUBLIC_DIR').'/'.dirname($issue->getImageFilePath());
            if (!file_exists($targetFolder)) {
                mkdir($targetFolder, 0777, true);
            }
            if (!$file->move($targetFolder, $issue->getImageFilename())) {
                return $this->fail(static::ISSUE_FILE_UPLOAD_FAILED);
            }
        }

        //if create, need to enfore correct GUID
        if ('create' === $mode) {
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
     * @Route("/delete", name="api_issue_delete")
     *
     * @param Request             $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface  $validator
     * @param IssueTransformer    $issueTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function issueDeleteAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest(
            $request,
            $serializer,
            $validator,
            $issueTransformer,
            function ($issue) {
                /** @var Issue $issue */
                if (null === $issue->getRegisteredAt()) {
                    $this->fastRemove($issue);

                    return $this->success(new EmptyData());
                }

                return $this->fail(static::ISSUE_ACTION_NOT_ALLOWED);
            }
        );
    }

    /**
     * @Route("/mark", name="api_issue_mark")
     *
     * @param Request             $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface  $validator
     * @param IssueTransformer    $issueTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function issueMarkAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest(
            $request,
            $serializer,
            $validator,
            $issueTransformer,
            function ($issue) {
                /* @var Issue $issue */
                $issue->setIsMarked(!$issue->getIsMarked());
                $this->fastSave($issue);

                return true;
            }
        );
    }

    /**
     * @Route("/review", name="api_issue_review")
     *
     * @param Request             $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface  $validator
     * @param IssueTransformer    $issueTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function issueReviewAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest(
            $request,
            $serializer,
            $validator,
            $issueTransformer,
            function ($issue, $constructionManager) {
                /** @var Issue $issue */
                /* @var ConstructionManager $constructionManager */
                if (null !== $issue->getRegisteredAt() && null === $issue->getReviewedAt()) {
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
     * @Route("/revert", name="api_issue_revert")
     *
     * @param Request             $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface  $validator
     * @param IssueTransformer    $issueTransformer
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function issueRevertAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer)
    {
        return $this->processIssueActionRequest(
            $request,
            $serializer,
            $validator,
            $issueTransformer,
            function ($issue) {
                /** @var Issue $issue */
                if (null !== $issue->getRegisteredAt()) {
                    if (null !== $issue->getReviewedAt()) {
                        $issue->setReviewedAt(null);
                        $issue->setReviewBy(null);
                    } elseif (null !== $issue->getRespondedAt()) {
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
     * @param Request             $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface  $validator
     * @param IssueTransformer    $issueTransformer
     * @param $action
     *
     * @throws ORMException
     *
     * @return JsonResponse|Response
     */
    private function processIssueActionRequest(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, IssueTransformer $issueTransformer, $action)
    {
        //check if empty request
        if (!($content = $request->getContent())) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var IssueActionRequest $issueActionRequest */
        $issueActionRequest = $serializer->deserialize($content, IssueActionRequest::class, 'json');

        // check all properties defined
        $errors = $validator->validate($issueActionRequest);
        if (count($errors) > 0) {
            return $this->fail(static::REQUEST_VALIDATION_FAILED);
        }

        //check auth token
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(AuthenticationToken::class)->getConstructionManager($issueActionRequest);
        if (null === $constructionManager) {
            return $this->fail(static::AUTHENTICATION_TOKEN_INVALID);
        }

        //get issue
        /** @var Issue $issue */
        $issue = $this->getDoctrine()->getRepository(Issue::class)->find($issueActionRequest->getIssueID());
        if (null === $issue) {
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
