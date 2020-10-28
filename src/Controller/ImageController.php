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
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
}
