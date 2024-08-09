<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use App\Controller\Base\BaseController;
use App\Controller\Traits\FileResponseTrait;
use App\Controller\Traits\ImageRequestTrait;
use App\Entity\ConstructionSite;
use App\Entity\ConstructionSiteImage;
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Entity\ProtocolEntry;
use App\Entity\ProtocolEntryFile;
use App\Helper\DoctrineHelper;
use App\Security\TokenTrait;
use App\Security\Voter\ConstructionSiteVoter;
use App\Security\Voter\IssueVoter;
use App\Security\Voter\MapVoter;
use App\Security\Voter\ProtocolEntryVoter;
use App\Service\Interfaces\CacheServiceInterface;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\StorageServiceInterface;
use App\Service\MapFileService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route(path: '/api')]
class ApiController extends BaseController
{
    use TokenTrait;
    use FileResponseTrait;
    use ImageRequestTrait;

    #[Route(path: '/me', name: 'api_me')]
    public function me(TokenStorageInterface $tokenStorage, IriConverterInterface $iriConverter): JsonResponse
    {
        $data = [];
        $token = $tokenStorage->getToken();

        $constructionManager = $this->tryGetConstructionManager($token);
        if ($constructionManager) {
            $data['constructionManagerIri'] = $iriConverter->getIriFromResource($constructionManager);
        }

        $craftsman = $this->tryGetCraftsman($token);
        if ($craftsman) {
            $data['craftsmanIri'] = $iriConverter->getIriFromResource($craftsman);
            $data['constructionSiteIri'] = $iriConverter->getIriFromResource($craftsman->getConstructionSite());
        }

        $filter = $this->tryGetFilter($token);
        if ($filter) {
            $data['filterIri'] = $iriConverter->getIriFromResource($filter);
            $data['constructionSiteIri'] = $iriConverter->getIriFromResource($filter->getConstructionSite());
        }

        return $this->json($data);
    }

    #[Route(path: '/status', name: 'api_status')]
    public function status(): JsonResponse
    {
        $data = [];

        /*
         * $applicationVersion = $request->headers->get('X-APPLICATION-VERSION');
         * list($os, $version) = explode('_', $applicationVersion);
         * $data['messageDe'] = "Bitte aktualisieren Sie die neue App";
         * $data['messageIt'] = "Pro favore ...";
         */

        return $this->json($data);
    }

    #[Route(path: '/maps/{map}/file/{mapFile}/{filename}', name: 'map_file', methods: ['GET'])]
    public function getMapFile(Request $request, Map $map, MapFile $mapFile, string $filename, PathServiceInterface $pathService, MapFileService $mapFileService): BinaryFileResponse
    {
        if ($map->getFile() !== $mapFile || $mapFile->getFilename() !== $filename) {
            throw new NotFoundHttpException();
        }

        $sanitizedVariant = strtolower($request->query->get('variant', ''));
        if ('' !== $sanitizedVariant && 'ios' !== $sanitizedVariant) {
            throw new NotFoundHttpException();
        }

        $path = $pathService->getFolderForMapFiles($mapFile->getCreatedFor()->getConstructionSite()).\DIRECTORY_SEPARATOR.$mapFile->getFilename();

        if ('ios' === $sanitizedVariant) {
            $optimized = $mapFileService->renderForMobileDevice($mapFile);
            if (null !== $optimized) {
                $path = $optimized;
            }
        }

        return $this->tryCreateAttachmentFileResponse($path, $mapFile->getFilename());
    }

    #[Route(path: '/maps/{map}/file/{mapFile}/{filename}/render.jpg', name: 'map_file_render', methods: ['GET'])]
    public function getMapFileRender(Request $request, Map $map, MapFile $mapFile, string $filename, ImageServiceInterface $imageService): BinaryFileResponse
    {
        if ($map->getFile() !== $mapFile || $mapFile->getFilename() !== $filename) {
            throw new NotFoundHttpException();
        }

        $size = $this->getValidImageSizeFromQuery($request->query);
        $path = $imageService->renderMapFileToJpg($mapFile, $size);

        return $this->tryCreateInlineFileResponse($path, 'render.jpg', true);
    }

    #[Route(path: '/maps/{map}/file', name: 'post_map_file', methods: ['POST'])]
    public function postMapFile(Request $request, Map $map, StorageServiceInterface $storageService, CacheServiceInterface $cacheService, ManagerRegistry $registry): Response
    {
        $this->denyAccessUnlessGranted(MapVoter::MAP_MODIFY, $map);

        $file = $this->getPdf($request->files);

        $mapFile = $storageService->uploadMapFile($file, $map);
        if (!$mapFile instanceof MapFile) {
            throw new BadRequestException('The map file could not be stored');
        }

        DoctrineHelper::persistAndFlush($registry, $map, $mapFile);
        $cacheService->warmUpCacheForMapFile($mapFile);

        $url = $this->generateUrl('map_file', ['map' => $map->getId(), 'mapFile' => $mapFile->getId(), 'filename' => $mapFile->getFilename()]);

        return new Response($url, Response::HTTP_CREATED);
    }

    #[Route(path: '/maps/{map}/file', name: 'delete_map_file', methods: ['DELETE'])]
    public function deleteMapFile(Map $map, ManagerRegistry $registry): Response
    {
        $this->denyAccessUnlessGranted(MapVoter::MAP_MODIFY, $map);

        $map->setFile(null);
        DoctrineHelper::persistAndFlush($registry, $map);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/construction_sites/{constructionSite}/image/{constructionSiteImage}/{filename}', name: 'construction_site_image', methods: ['GET'])]
    public function getConstructionSiteImage(Request $request, ConstructionSite $constructionSite, ConstructionSiteImage $constructionSiteImage, string $filename, ImageServiceInterface $imageService): BinaryFileResponse
    {
        if ($constructionSite->getImage() !== $constructionSiteImage || $constructionSiteImage->getFilename() !== $filename) {
            throw new NotFoundHttpException();
        }

        $size = $this->getValidImageSizeFromQuery($request->query);
        $path = $imageService->resizeConstructionSiteImage($constructionSiteImage, $size);

        return $this->tryCreateInlineFileResponse($path, $constructionSiteImage->getFilename(), true);
    }

    #[Route(path: '/construction_sites/{constructionSite}/image', name: 'post_construction_site_image', methods: ['POST'])]
    public function postConstructionSiteImage(Request $request, ConstructionSite $constructionSite, StorageServiceInterface $storageService, CacheServiceInterface $cacheService, ManagerRegistry $registry): Response
    {
        $this->denyAccessUnlessGranted(ConstructionSiteVoter::CONSTRUCTION_SITE_MODIFY, $constructionSite);

        $image = $this->getImage($request->files);

        $constructionSiteImage = $storageService->uploadConstructionSiteImage($image, $constructionSite);
        if (!$constructionSiteImage instanceof ConstructionSiteImage) {
            throw new BadRequestException('The construction site image could not be stored');
        }

        DoctrineHelper::persistAndFlush($registry, $constructionSite, $constructionSiteImage);
        $cacheService->warmUpCacheForConstructionSiteImage($constructionSiteImage);

        $url = $this->generateUrl('construction_site_image', ['constructionSite' => $constructionSite->getId(), 'constructionSiteImage' => $constructionSiteImage->getId(), 'filename' => $constructionSiteImage->getFilename()]);

        return new Response($url, Response::HTTP_CREATED);
    }

    #[Route(path: '/construction_sites/{constructionSite}/image', name: 'delete_construction_site_image', methods: ['DELETE'])]
    public function deleteConstructionSiteImage(ConstructionSite $constructionSite, ManagerRegistry $registry): Response
    {
        $this->denyAccessUnlessGranted(ConstructionSiteVoter::CONSTRUCTION_SITE_MODIFY, $constructionSite);

        $constructionSite->setImage(null);
        DoctrineHelper::persistAndFlush($registry, $constructionSite);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/protocol_entries/{protocolEntry}/file', name: 'post_protocol_entry_file', methods: ['POST'])]
    public function postProtocolEntryFile(Request $request, ProtocolEntry $protocolEntry, StorageServiceInterface $storageService, ImageServiceInterface $imageService, CacheServiceInterface $cacheService, ManagerRegistry $registry): Response
    {
        $this->denyAccessUnlessGranted(ProtocolEntryVoter::PROTOCOL_ENTRY_MODIFY, $protocolEntry);

        $file = $this->getSafeFile($request->files);

        $protocolEntryFile = $storageService->uploadProtocolEntryFile($file, $protocolEntry);
        if (!$protocolEntryFile instanceof ProtocolEntryFile) {
            throw new BadRequestException('The protocol entry file could not be stored');
        }

        DoctrineHelper::persistAndFlush($registry, $protocolEntry, $protocolEntryFile);
        $cacheService->warmUpCacheForProtocolEntryFile($protocolEntryFile);

        $url = $this->generateUrl('protocol_entry_file', ['protocolEntry' => $protocolEntry->getId(), 'protocolEntryFile' => $protocolEntryFile->getId(), 'filename' => $protocolEntryFile->getFilename()]);

        return new Response($url, Response::HTTP_CREATED);
    }

    #[Route(path: '/issues/{issue}/image/{issueImage}/{filename}', name: 'issue_image', methods: ['GET'])]
    public function getIssueImage(Request $request, Issue $issue, IssueImage $issueImage, string $filename, ImageServiceInterface $imageService): BinaryFileResponse
    {
        if ($issue->getImage() !== $issueImage || $issueImage->getFilename() !== $filename) {
            throw new NotFoundHttpException();
        }

        $size = $this->getValidImageSizeFromQuery($request->query);
        $path = $imageService->resizeIssueImage($issueImage, $size);

        return $this->tryCreateInlineFileResponse($path, $issueImage->getFilename(), true);
    }

    #[Route(path: '/protocol_entries/{protocolEntry}/file/{protocolEntryFile}/{filename}', name: 'protocol_entry_file', methods: ['GET'])]
    public function getProtocolEntryFile(Request $request, ProtocolEntry $protocolEntry, ProtocolEntryFile $protocolEntryFile, string $filename, ImageServiceInterface $imageService, PathServiceInterface $pathService): BinaryFileResponse
    {
        if ($protocolEntry->getFile() !== $protocolEntryFile || $protocolEntryFile->getFilename() !== $filename) {
            throw new NotFoundHttpException();
        }

        if ($imageService->isImageFilename($filename)) {
            $size = $this->getValidImageSizeFromQuery($request->query);
            $path = $imageService->resizeProtocolEntryImage($protocolEntryFile, $size);

            return $this->tryCreateInlineFileResponse($path, $protocolEntryFile->getFilename(), true);
        }

        $path = $pathService->getFolderForProtocolEntryFiles($protocolEntryFile->getCreatedFor()->getConstructionSite()).\DIRECTORY_SEPARATOR.$protocolEntryFile->getFilename();

        return $this->tryCreateAttachmentFileResponse($path, $protocolEntryFile->getFilename());
    }

    #[Route(path: '/issues/{issue}/map/render.jpg', name: 'issue_map_render', methods: ['GET'])]
    public function getIssueRender(Request $request, Issue $issue, ImageServiceInterface $imageService): BinaryFileResponse
    {
        $mapFile = $issue->getMap()->getFile();
        if (!$mapFile instanceof MapFile) {
            throw new NotFoundHttpException();
        }

        $size = $this->getValidImageSizeFromQuery($request->query);
        $path = $imageService->renderMapFileWithSingleIssueToJpg($mapFile, $issue, $size);

        return $this->tryCreateInlineFileResponse($path, 'render.jpg', false);
    }

    #[Route(path: '/issues/{issue}/image', name: 'post_issue_image', methods: ['POST'])]
    public function postIssueImage(Request $request, Issue $issue, StorageServiceInterface $storageService, CacheServiceInterface $cacheService, ManagerRegistry $registry): Response
    {
        $this->denyAccessUnlessGranted(IssueVoter::ISSUE_MODIFY, $issue);

        $image = $this->getImage($request->files);

        $issueImage = $storageService->uploadIssueImage($image, $issue);
        if (!$issueImage instanceof IssueImage) {
            throw new BadRequestException('The issue site image could not be stored');
        }

        DoctrineHelper::persistAndFlush($registry, $issue, $issueImage);
        $cacheService->warmUpCacheForIssueImage($issueImage);

        $url = $this->generateUrl('issue_image', ['issue' => $issue->getId(), 'issueImage' => $issueImage->getId(), 'filename' => $issueImage->getFilename()]);

        return new Response($url, Response::HTTP_CREATED);
    }

    #[Route(path: '/issues/{issue}/image', name: 'delete_issue_image', methods: ['DELETE'])]
    public function deleteIssueImage(Issue $issue, ManagerRegistry $registry): Response
    {
        $this->denyAccessUnlessGranted(IssueVoter::ISSUE_MODIFY, $issue);

        $issue->setImage(null);
        DoctrineHelper::persistAndFlush($registry, $issue);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function getSafeFile(FileBag $fileBag): UploadedFile
    {
        return $this->getUploadedFile($fileBag, 'file', [
            'application/pdf', 'application/x-pdf', // pdf
            'image/jpeg', 'image/gif', 'image/png', // gif
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // word
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // excel
            'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', // presentation
            'application/vnd.ms-outlook', 'message/rfc822', // emails
            'text/html', 'text/plain', // text or plain
            'application/zip', // general
        ], [
            'eml', 'msg', // emails
        ]);
    }

    private function getPdf(FileBag $fileBag): UploadedFile
    {
        return $this->getUploadedFile($fileBag, 'file', ['application/pdf', 'application/x-pdf']);
    }

    private function getImage(FileBag $fileBag): UploadedFile
    {
        return $this->getUploadedFile($fileBag, 'image', ['image/jpeg', 'image/gif', 'image/png']);
    }

    private function getUploadedFile(FileBag $fileBag, string $key, array $mimeTypesWhitelist, array $octetWhitelist = []): UploadedFile
    {
        if ($fileBag->has($key)) {
            // as its a file, have to use all method
            $candidate = $fileBag->get($key);
        } elseif (1 === $fileBag->count()) {
            $files = $fileBag->all();
            $candidate = $files[array_key_first($files)];
        } else {
            throw new BadRequestException('More than one file uploaded at a time is not allowed');
        }

        /** @var UploadedFile $candidate */
        if (in_array($candidate->getMimeType(), $mimeTypesWhitelist)) {
            return $candidate;
        } elseif ('application/octet-stream' === $candidate->getMimeType() && in_array($candidate->getExtension(), $octetWhitelist)) {
            return $candidate;
        } else {
            throw new BadRequestException('Unexpected mimeType: '.$candidate->getMimeType());
        }
    }
}
