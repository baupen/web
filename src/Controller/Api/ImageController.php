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

use App\Controller\Base\BaseDoctrineController;
use App\Entity\ConstructionSite;
use App\Security\Voter\ConstructionSiteVoter;
use App\Service\Interfaces\StorageServiceInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api")
 */
class ImageController extends BaseDoctrineController
{
    /**
     * @Route("/construction_sites/{constructionSite}/image", name="post_construction_site_image", methods={"POST"})
     *
     * @return Response
     */
    public function getConstructionSiteImageAction(Request $request, ConstructionSite $constructionSite, StorageServiceInterface $storageService, SerializerInterface $serializer)
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

        $json = $serializer->serialize($constructionSiteImage, 'json');

        return new JsonResponse($json);
    }
}
