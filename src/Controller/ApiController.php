<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Controller\Base\BaseDoctrineController;
use App\Controller\Traits\FileResponseTrait;
use App\Controller\Traits\ImageRequestTrait;
use App\Entity\ConstructionSite;
use App\Entity\ConstructionSiteImage;
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Security\TokenTrait;
use App\Security\Voter\ConstructionSiteVoter;
use App\Security\Voter\IssueVoter;
use App\Security\Voter\MapVoter;
use App\Service\Interfaces\CacheServiceInterface;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\StorageServiceInterface;
use App\Service\MapFileService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/api")
 */
class ApiController extends BaseDoctrineController
{
    use TokenTrait;
    use FileResponseTrait;
    use ImageRequestTrait;

    /**
     * @Route("/me", name="api_me")
     *
     * @return Response
     */
    public function meAction(TokenStorageInterface $tokenStorage, IriConverterInterface $iriConverter)
    {
        $data = [];
        $token = $tokenStorage->getToken();

        $constructionManager = $this->tryGetConstructionManager($token);
        if (null !== $constructionManager) {
            $data['constructionManagerIri'] = $iriConverter->getIriFromItem($constructionManager);
        }

        $craftsman = $this->tryGetCraftsman($token);
        if (null !== $craftsman) {
            $data['craftsmanIri'] = $iriConverter->getIriFromItem($craftsman);
            $data['constructionSiteIri'] = $iriConverter->getIriFromItem($craftsman->getConstructionSite());
        }

        $filter = $this->tryGetFilter($token);
        if (null !== $filter) {
            $data['filterIri'] = $iriConverter->getIriFromItem($filter);
            $data['constructionSiteIri'] = $iriConverter->getIriFromItem($filter->getConstructionSite());
        }

        return $this->json($data);
    }

    /**
     * @Route("/status", name="api_status")
     *
     * @return Response
     */
    public function statusAction()
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

    /**
     * @Route("/maps/{map}/file/{mapFile}/{filename}", name="map_file", methods={"GET"})
     *
     * @return Response
     */
    public function getMapFileAction(Request $request, Map $map, MapFile $mapFile, string $filename, PathServiceInterface $pathService, MapFileService $mapFileService)
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

    /**
     * @Route("/maps/{map}/file/{mapFile}/{filename}/render.jpg", name="map_file_render", methods={"GET"})
     *
     * @return Response
     */
    public function getMapFileRenderAction(Request $request, Map $map, MapFile $mapFile, string $filename, ImageServiceInterface $imageService)
    {
        if ($map->getFile() !== $mapFile || $mapFile->getFilename() !== $filename) {
            throw new NotFoundHttpException();
        }

        $size = $this->getValidImageSizeFromQuery($request->query);
        $path = $imageService->renderMapFileToJpg($mapFile, $size);

        return $this->tryCreateInlineFileResponse($path, 'render.jpg');
    }

    /**
     * @Route("/maps/{map}/file", name="post_map_file", methods={"POST"})
     *
     * @return Response
     */
    public function postMapFile(Request $request, Map $map, StorageServiceInterface $storageService, CacheServiceInterface $cacheService)
    {
        $this->denyAccessUnlessGranted(MapVoter::MAP_MODIFY, $map);

        $file = $this->getPdf($request->files);

        $mapFile = $storageService->uploadMapFile($file, $map);
        if (null === $mapFile) {
            throw new BadRequestException();
        }

        $this->fastSave($map, $mapFile);
        $cacheService->warmUpCacheForMapFile($mapFile);

        $url = $this->generateUrl('map_file', ['map' => $map->getId(), 'mapFile' => $mapFile->getId(), 'filename' => $mapFile->getFilename()]);

        return new Response($url, Response::HTTP_CREATED);
    }

    /**
     * @Route("/maps/{map}/file", name="delete_map_file", methods={"DELETE"})
     *
     * @return Response
     */
    public function deleteMapFileAction(Map $map)
    {
        $this->denyAccessUnlessGranted(MapVoter::MAP_MODIFY, $map);

        $map->setFile(null);
        $this->fastSave($map);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/construction_sites/{constructionSite}/image/{constructionSiteImage}/{filename}", name="construction_site_image", methods={"GET"})
     *
     * @return Response
     */
    public function getConstructionSiteImageAction(Request $request, ConstructionSite $constructionSite, ConstructionSiteImage $constructionSiteImage, string $filename, ImageServiceInterface $imageService)
    {
        if ($constructionSite->getImage() !== $constructionSiteImage || $constructionSiteImage->getFilename() !== $filename) {
            throw new NotFoundHttpException();
        }

        $size = $this->getValidImageSizeFromQuery($request->query);
        $path = $imageService->resizeConstructionSiteImage($constructionSiteImage, $size);

        return $this->tryCreateInlineFileResponse($path, $constructionSiteImage->getFilename(), true);
    }

    /**
     * @Route("/construction_sites/{constructionSite}/image", name="post_construction_site_image", methods={"POST"})
     *
     * @return Response
     */
    public function postConstructionSiteImageAction(Request $request, ConstructionSite $constructionSite, StorageServiceInterface $storageService, CacheServiceInterface $cacheService)
    {
        $this->denyAccessUnlessGranted(ConstructionSiteVoter::CONSTRUCTION_SITE_MODIFY, $constructionSite);

        $image = $this->getImage($request->files);

        $constructionSiteImage = $storageService->uploadConstructionSiteImage($image, $constructionSite);
        if (null === $constructionSiteImage) {
            throw new BadRequestException();
        }

        $this->fastSave($constructionSite, $constructionSiteImage);
        $cacheService->warmUpCacheForConstructionSiteImage($constructionSiteImage);

        $url = $this->generateUrl('construction_site_image', ['constructionSite' => $constructionSite->getId(), 'constructionSiteImage' => $constructionSiteImage->getId(), 'filename' => $constructionSiteImage->getFilename()]);

        return new Response($url, Response::HTTP_CREATED);
    }

    /**
     * @Route("/construction_sites/{constructionSite}/image", name="delete_construction_site_image", methods={"DELETE"})
     *
     * @return Response
     */
    public function deleteConstructionSiteImageAction(ConstructionSite $constructionSite)
    {
        $this->denyAccessUnlessGranted(ConstructionSiteVoter::CONSTRUCTION_SITE_MODIFY, $constructionSite);

        $constructionSite->setImage(null);
        $this->fastSave($constructionSite);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/issues/{issue}/image/{issueImage}/{filename}", name="issue_image", methods={"GET"})
     *
     * @return Response
     */
    public function getIssueImageAction(Request $request, Issue $issue, IssueImage $issueImage, string $filename, ImageServiceInterface $imageService)
    {
        if ($issue->getImage() !== $issueImage || $issueImage->getFilename() !== $filename) {
            throw new NotFoundHttpException();
        }

        $size = $this->getValidImageSizeFromQuery($request->query);
        $path = $imageService->resizeIssueImage($issueImage, $size);

        return $this->tryCreateInlineFileResponse($path, $issueImage->getFilename(), true);
    }

    /**
     * @Route("/issues/{issue}/image", name="post_issue_image", methods={"POST"})
     *
     * @return Response
     */
    public function postIssueImageAction(Request $request, Issue $issue, StorageServiceInterface $storageService, CacheServiceInterface $cacheService)
    {
        $this->denyAccessUnlessGranted(IssueVoter::ISSUE_MODIFY, $issue);

        $image = $this->getImage($request->files);

        $issueImage = $storageService->uploadIssueImage($image, $issue);
        if (null === $issueImage) {
            throw new BadRequestException();
        }

        $this->fastSave($issue, $issueImage);
        $cacheService->warmUpCacheForIssueImage($issueImage);

        $url = $this->generateUrl('issue_image', ['issue' => $issue->getId(), 'issueImage' => $issueImage->getId(), 'filename' => $issueImage->getFilename()]);

        return new Response($url, Response::HTTP_CREATED);
    }

    /**
     * @Route("/issues/{issue}/image", name="delete_issue_image", methods={"DELETE"})
     *
     * @return Response
     */
    public function deleteIssueImageAction(Issue $issue)
    {
        $this->denyAccessUnlessGranted(IssueVoter::ISSUE_MODIFY, $issue);

        $issue->setImage(null);
        $this->fastSave($issue);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function getPdf(FileBag $fileBag): UploadedFile
    {
        return $this->getUploadedFile($fileBag, 'file', ['application/pdf']);
    }

    private function getImage(FileBag $fileBag): UploadedFile
    {
        return $this->getUploadedFile($fileBag, 'image', ['image/jpeg', 'image/gif', 'image/png']);
    }

    private function getUploadedFile(FileBag $fileBag, string $key, array $mimeTypesWhitelist): UploadedFile
    {
        if ($fileBag->has($key)) {
            // as its a file, have to use all method
            $candidate = $fileBag->get($key);
        } elseif (1 === $fileBag->count()) {
            $files = $fileBag->all();
            $candidate = $files[array_key_first($files)];
        } else {
            throw new BadRequestException();
        }

        if (!in_array($candidate->getMimeType(), $mimeTypesWhitelist)) {
            throw new BadRequestException();
        }

        return $candidate;
    }
}
