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
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\HttpFoundation\Response;
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
        $this->denyAccessUnlessGranted($constructionSite);
        if ($constructionSiteImage->getConstructionSite() !== $constructionSite) {
            throw new NotFoundHttpException();
        }

        if (!in_array($size, ImageServiceInterface::VALID_SIZES)) {
            throw new NotFoundHttpException();
        }

        return $imageService->resizeConstructionSiteImage($constructionSiteImage, $size);
    }
}
