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
use App\Security\Voter\ConstructionSiteVoter;
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
     * @Route("/construction_site/{constructionSite}/image/{constructionSiteImage}/{size}", name="construction_site_image", defaults={"size"="thumbnail"}, methods={"GET"})
     *
     * @return Response
     */
    public function getConstructionSiteImageAction(ConstructionSite $constructionSite, ConstructionSiteImage $constructionSiteImage, string $size, ImageServiceInterface $imageService)
    {
        $this->denyAccessUnlessGranted(ConstructionSiteVoter::CONSTRUCTION_SITE_VIEW, $constructionSite);
        if ($constructionSiteImage->getConstructionSite() !== $constructionSite) {
            throw new NotFoundHttpException();
        }

        if (!in_array($size, ImageServiceInterface::VALID_SIZES)) {
            throw new NotFoundHttpException();
        }

        $path = $imageService->resizeConstructionSiteImage($constructionSiteImage, $size);
        if (null === $path) {
            throw new NotFoundHttpException();
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $constructionSiteImage->getFilename()
        );

        return $response;
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
        $this->fastSave($constructionSite, $constructionSiteImage);
        $cacheService->warmUpCacheForConstructionSiteImage($constructionSiteImage);

        return new Response($constructionSiteImage->getId());
    }
}
