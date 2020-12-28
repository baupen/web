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

use App\Controller\Base\BaseDoctrineController;
use App\Entity\ConstructionSite;
use App\Entity\ConstructionSiteImage;
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\Traits\FileTrait;
use App\Security\Voter\ConstructionSiteVoter;
use App\Security\Voter\IssueVoter;
use App\Service\Interfaces\CacheServiceInterface;
use App\Service\Interfaces\ImageServiceInterface;
use App\Service\Interfaces\StorageServiceInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ApiImageController extends BaseDoctrineController
{
    /**
     * @Route("/construction_sites/{constructionSite}/image/{constructionSiteImage}/{filename}", name="construction_site_image", methods={"GET"})
     *
     * @return Response
     */
    public function getConstructionSiteImageAction(Request $request, ConstructionSite $constructionSite, ConstructionSiteImage $constructionSiteImage, string $filename, ImageServiceInterface $imageService)
    {
        $this->denyAccessUnlessGranted(ConstructionSiteVoter::CONSTRUCTION_SITE_VIEW, $constructionSite);
        if ($constructionSite->getImage() !== $constructionSiteImage || $constructionSiteImage->getFilename() !== $filename) {
            throw new NotFoundHttpException();
        }

        $size = $request->query->get('size', 'thumbnail');
        $this->assertValidSize($size);
        $path = $imageService->resizeConstructionSiteImage($constructionSiteImage, $size);

        return $this->tryCreateInlineFileResponse($path, $constructionSiteImage);
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

        if ($constructionSite->getImage()) {
            $this->fastRemove($constructionSite->getImage());
        }

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
     * @Route("/issues/{issue}/image/{issueImage}/{filename}", name="issue_image", methods={"GET"})
     *
     * @return Response
     */
    public function getIssueImageAction(Request $request, Issue $issue, IssueImage $issueImage, string $filename, ImageServiceInterface $imageService)
    {
        $this->denyAccessUnlessGranted(IssueVoter::ISSUE_VIEW, $issue);
        if ($issue->getImage() !== $issueImage || $issueImage->getFilename() !== $filename) {
            throw new NotFoundHttpException();
        }

        $size = $request->query->get('size', 'thumbnail');
        $this->assertValidSize($size);
        $path = $imageService->resizeIssueImage($issueImage, $size);

        return $this->tryCreateInlineFileResponse($path, $issueImage);
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

        if ($issue->getImage()) {
            $this->fastRemove($issue->getImage());
        }

        $issueImage = $storageService->uploadIssueImage($image, $issue);
        if (null === $issueImage) {
            throw new BadRequestException();
        }

        $this->fastSave($issue, $issueImage);
        $cacheService->warmUpCacheForIssueImage($issueImage);

        $url = $this->generateUrl('issue_image', ['issue' => $issue->getId(), 'issueImage' => $issueImage->getId(), 'filename' => $issueImage->getFilename()]);

        return new Response($url, Response::HTTP_CREATED);
    }

    private function assertValidSize(string $size): void
    {
        if (!in_array($size, ImageServiceInterface::VALID_SIZES)) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @param FileTrait $file
     */
    private function tryCreateInlineFileResponse(?string $path, $file): BinaryFileResponse
    {
        if (null === $path) {
            throw new NotFoundHttpException();
        }

        $response = new BinaryFileResponse($path);

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $file->getFilename()
        );

        return $response;
    }

    private function getImage(FileBag $fileBag): UploadedFile
    {
        if ($fileBag->has('image')) {
            $candidate = $fileBag->get('image');
        } elseif (1 === $fileBag->count()) {
            $files = $fileBag->all();
            $candidate = $files[array_key_first($files)];
        } else {
            throw new BadRequestException();
        }

        if (!in_array($candidate->getMimeType(), ['image/jpeg', 'image/gif', 'image/png'])) {
            throw new BadRequestException();
        }

        return $candidate;
    }
}
