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

use App\Api\Entity\ObjectMeta;
use App\Api\Request\DownloadFileRequest;
use App\Controller\Api\Base\BaseApiController;
use App\Entity\AuthenticationToken;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Entity\Map;
use App\Entity\Traits\TimeTrait;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/file")
 *
 * @return Response
 */
class FileController extends BaseApiController
{
    /**
     * @Route("/download", name="api_file_download")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     *
     * @throws ORMException
     *
     * @return Response
     */
    public function fileDownloadAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        //check if empty request
        if (!($content = $request->getContent())) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var DownloadFileRequest $downloadFileRequest */
        $downloadFileRequest = $serializer->deserialize($content, DownloadFileRequest::class, 'json');

        // check all properties defined
        $errors = $validator->validate($downloadFileRequest);
        if (count($errors) > 0) {
            return $this->fail(static::REQUEST_VALIDATION_FAILED);
        }

        //check auth token
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(AuthenticationToken::class)->getConstructionManager($downloadFileRequest);
        if (null === $constructionManager) {
            return $this->fail(static::AUTHENTICATION_TOKEN_INVALID);
        }

        //get file
        if (null !== $downloadFileRequest->getMap()) {
            return $this->downloadFile(
                $this->getDoctrine()->getRepository(Map::class),
                $downloadFileRequest->getMap(),
                function ($entity) use ($constructionManager) {
                    /* @var Map $entity */
                    return $entity->getConstructionSite()->getConstructionManagers()->contains($constructionManager);
                },
                function ($entity) {
                    /* @var Map $entity */
                    return $entity->getFilePath();
                }
            );
        } elseif (null !== $downloadFileRequest->getIssue()) {
            return $this->downloadFile(
                $this->getDoctrine()->getRepository(Issue::class),
                $downloadFileRequest->getIssue(),
                function ($entity) use ($constructionManager) {
                    /* @var Issue $entity */
                    return $entity->getMap()->getConstructionSite()->getConstructionManagers()->contains($constructionManager);
                },
                function ($entity) {
                    /* @var Issue $entity */
                    return $entity->getImageFilePath();
                }
            );
        } elseif (null !== $downloadFileRequest->getBuilding()) {
            return $this->downloadFile(
                $this->getDoctrine()->getRepository(ConstructionSite::class),
                $downloadFileRequest->getBuilding(),
                function ($entity) use ($constructionManager) {
                    /* @var ConstructionSite $entity */
                    return $entity->getConstructionManagers()->contains($constructionManager);
                },
                function ($entity) {
                    /* @var ConstructionSite $entity */
                    return $entity->getImageFilePath();
                }
            );
        }

        //construct answer
        return $this->fail(static::REQUEST_VALIDATION_FAILED);
    }

    /**
     * @param ObjectRepository $repository
     * @param ObjectMeta $objectMeta
     * @param callable $verifyAccess
     * @param callable $accessFilePath
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|Response
     */
    private function downloadFile(ObjectRepository $repository, ObjectMeta $objectMeta, callable $verifyAccess, callable $accessFilePath)
    {
        /** @var ObjectMeta $objectMeta */
        /** @var EntityRepository $repository */
        $entity = $repository->find($objectMeta->getId());

        /** @var TimeTrait $entity */
        if (null === $entity) {
            return $this->fail(static::ENTITY_NO_DOWNLOADABLE_FILE);
        }

        if (!$verifyAccess($entity)) {
            return $this->fail(static::ENTITY_ACCESS_DENIED);
        }

        if ($entity->getLastChangedAt() > new \DateTime($objectMeta->getLastChangeTime())) {
            return $this->fail(static::INVALID_TIMESTAMP);
        }

        $filePath = $accessFilePath($entity);
        if (null === $filePath) {
            return $this->fail(static::ENTITY_ACCESS_DENIED);
        }

        $filePath = $this->getParameter('PUBLIC_DIR') . '/' . $filePath;
        if (!file_exists($filePath)) {
            return $this->fail(static::ENTITY_FILE_NOT_FOUND);
        }

        return $this->file($filePath);
    }
}
