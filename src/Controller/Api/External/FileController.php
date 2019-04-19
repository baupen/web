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
use App\Service\Interfaces\PathServiceInterface;
use DateTime;
use const DIRECTORY_SEPARATOR;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
     * @param PathServiceInterface $pathService
     * @param ImageServiceInterface $imageService
     *
     * @throws ORMException
     * @throws Exception
     *
     * @return Response
     */
    public function fileDownloadAction(Request $request, PathServiceInterface $pathService, ImageServiceInterface $imageService)
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
                function ($entity) use ($pathService) {
                    /* @var Map $entity */
                    return  $entity->getFile() ? $pathService->getFolderForMapFile($entity->getConstructionSite()) . DIRECTORY_SEPARATOR . $entity->getFile()->getFilename() : null;
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
                function ($entity) use ($imageService) {
                    /* @var Issue $entity */
                    return $imageService->getSizeForIssue($entity, ImageServiceInterface::SIZE_FULL);
                }
            );
        } elseif ($downloadFileRequest->getConstructionSite() !== null) {
            return $this->downloadFile(
                $this->getDoctrine()->getRepository(ConstructionSite::class),
                $downloadFileRequest->getConstructionSite(),
                function ($entity) use ($constructionManager) {
                    /* @var ConstructionSite $entity */
                    return $entity->getConstructionManagers()->contains($constructionManager);
                },
                function ($entity) use ($imageService) {
                    /* @var ConstructionSite $entity */
                    return $imageService->getSizeForConstructionSite($entity, ImageServiceInterface::SIZE_FULL);
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
     * @throws Exception
     *
     * @return BinaryFileResponse|Response
     */
    private function downloadFile(ObjectRepository $repository, ObjectMeta $objectMeta, callable $verifyAccess, callable $accessFilePath)
    {
        /** @var ObjectMeta $objectMeta */
        /** @var EntityRepository $repository */
        $entity = $repository->find($objectMeta->getId());

        /** @var TimeTrait|null $entity */
        if ($entity === null) {
            return $this->fail(static::ENTITY_NOT_FOUND);
        }

        if (!$verifyAccess($entity)) {
            return $this->fail(static::ENTITY_ACCESS_DENIED);
        }

        if ($entity->getLastChangedAt() > new DateTime($objectMeta->getLastChangeTime())) {
            return $this->fail(static::INVALID_TIMESTAMP);
        }

        $filePath = $accessFilePath($entity);
        if ($filePath === null) {
            return $this->fail(static::ENTITY_NO_DOWNLOADABLE_FILE);
        }

        if (!file_exists($filePath)) {
            return $this->fail(static::ENTITY_FILE_NOT_FOUND);
        }

        return $this->file($filePath);
    }
}
