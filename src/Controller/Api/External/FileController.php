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

use App\Api\External\Entity\ObjectMeta;
use App\Api\External\Request\DownloadFileRequest;
use App\Controller\Api\External\Base\ExternalApiController;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Entity\Map;
use App\Entity\Traits\TimeTrait;
use App\Service\Interfaces\ImageServiceInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/file")
 *
 * @return Response
 */
class FileController extends ExternalApiController
{
    const ENTITY_NOT_FOUND = 'entity was not found';
    const ENTITY_ACCESS_DENIED = 'you are not allowed to access this entity';
    const ENTITY_NO_DOWNLOADABLE_FILE = 'entity has no file to download';
    const ENTITY_FILE_NOT_FOUND = 'the server could not find the file of the entity';
    const INVALID_TIMESTAMP = 'invalid timestamp';

    /**
     * @Route("/download", name="api_external_file_download", methods={"POST"})
     *
     * @param Request $request
     * @param ImageServiceInterface $imageService
     *
     * @throws ORMException
     *
     * @return Response
     */
    public function fileDownloadAction(Request $request, ImageServiceInterface $imageService)
    {
        /** @var DownloadFileRequest $downloadFileRequest */
        /** @var ConstructionManager $constructionManager */
        if (!$this->parseAuthenticatedRequest($request, DownloadFileRequest::class, $downloadFileRequest, $errorResponse, $constructionManager)) {
            return $errorResponse;
        }

        //get file
        if ($downloadFileRequest->getMap() !== null) {
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
        } elseif ($downloadFileRequest->getIssue() !== null) {
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
                },
                $imageService
            );
        } elseif ($downloadFileRequest->getBuilding() !== null) {
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
                },
                $imageService
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
     * @param ImageServiceInterface|null $imageService pass if its an image, then resize
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|Response
     */
    private function downloadFile(ObjectRepository $repository, ObjectMeta $objectMeta, callable $verifyAccess, callable $accessFilePath, ImageServiceInterface $imageService = null)
    {
        /** @var ObjectMeta $objectMeta */
        /** @var EntityRepository $repository */
        $entity = $repository->find($objectMeta->getId());

        /** @var TimeTrait $entity */
        if ($entity === null) {
            return $this->fail(static::ENTITY_NOT_FOUND);
        }

        if (!$verifyAccess($entity)) {
            return $this->fail(static::ENTITY_ACCESS_DENIED);
        }

        if ($entity->getLastChangedAt() > new \DateTime($objectMeta->getLastChangeTime())) {
            return $this->fail(static::INVALID_TIMESTAMP);
        }

        $filePath = $accessFilePath($entity);
        if ($filePath === null) {
            return $this->fail(static::ENTITY_NO_DOWNLOADABLE_FILE);
        }

        $filePath = $this->getParameter('PUBLIC_DIR') . '/' . $filePath;
        if (!file_exists($filePath)) {
            return $this->fail(static::ENTITY_FILE_NOT_FOUND);
        }
        //resize images if image service passed
        if ($imageService !== null) {
            $filePath = $imageService->getSize($filePath, ImageServiceInterface::SIZE_FULL);
        }

        return $this->file($filePath);
    }
}
