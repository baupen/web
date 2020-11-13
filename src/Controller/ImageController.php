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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends BaseDoctrineController
{
    /**
     * @Route("/construction_sites/{constructionSite}/image/{constructionSiteImage}/{size}", name="construction_site_image", defaults={"size"="thumbnail"}, methods={"GET"})
     *
     * @return Response
     */
    public function getConstructionSiteImageAction(ConstructionSite $constructionSite, ConstructionSiteImage $constructionSiteImage, string $size, ImageServiceInterface $imageService)
    {
        $this->denyAccessUnlessGranted(ConstructionSiteVoter::CONSTRUCTION_SITE_VIEW, $constructionSite);
        if ($constructionSite->getImage() !== $constructionSiteImage) {
            throw new NotFoundHttpException();
        }

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
        if (1 !== $request->files->count()) {
            throw new BadRequestException();
        }

        if ($constructionSite->getImage()) {
            $this->fastRemove($constructionSite->getImage());
        }

        $files = $request->files->all();
        $file = $files[array_key_first($files)];
        $constructionSiteImage = $storageService->uploadConstructionSiteImage($file, $constructionSite);
        if (null === $constructionSiteImage) {
            throw new BadRequestException();
        }

        $this->fastSave($constructionSite, $constructionSiteImage);
        $cacheService->warmUpCacheForConstructionSiteImage($constructionSiteImage);

        return new Response($constructionSiteImage->getId());
    }

    /**
     * @Route("/issues/{issue}/image/{issueImage}/{size}", name="issue_image", defaults={"size"="thumbnail"}, methods={"GET"})
     *
     * @return Response
     */
    public function getIssueImageAction(Issue $issue, IssueImage $issueImage, string $size, ImageServiceInterface $imageService)
    {
        $this->denyAccessUnlessGranted(IssueVoter::ISSUE_VIEW, $issue);
        if ($issue->getImage() !== $issueImage) {
            throw new NotFoundHttpException();
        }

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
        if (1 !== $request->files->count()) {
            throw new BadRequestException();
        }

        if ($issue->getImage()) {
            $this->fastRemove($issue->getImage());
        }

        $files = $request->files->all();
        $file = $files[array_key_first($files)];
        $issueImage = $storageService->uploadIssueImage($file, $issue);
        if (null === $issueImage) {
            throw new BadRequestException();
        }

        $this->fastSave($issue, $issueImage);
        $cacheService->warmUpCacheForIssueImage($issueImage);

        return new Response($issueImage->getId());
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
}
