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

use App\Controller\Base\BaseController;
use App\Entity\MapFile;
use App\Security\Voter\Base\BaseVoter;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/map_file")
 */
class MapFileController extends BaseController
{
    /**
     * @Route("/{mapFile}/image", name="api_map_file_image")
     *
     * @param Request               $request
     * @param MapFile               $mapFile
     * @param ImageServiceInterface $imageService
     *
     * @return Response
     */
    public function imageAction(Request $request, MapFile $mapFile, ImageServiceInterface $imageService)
    {
        $this->denyAccessUnlessGranted(BaseVoter::ANY_ATTRIBUTE, $mapFile);

        $size = $request->query->get('size', ImageServiceInterface::SIZE_FULL);
        $filePath = $imageService->getMapFileImage($mapFile, $size);

        return $this->file($filePath);
    }

    /**
     * @Route("/{mapFile}/image/sector_frame", name="api_map_file_image_sector_frame")
     *
     * @param Request               $request
     * @param MapFile               $mapFile
     * @param ImageServiceInterface $imageService
     *
     * @return Response
     */
    public function imageSectorFrameAction(Request $request, MapFile $mapFile, ImageServiceInterface $imageService)
    {
        $this->denyAccessUnlessGranted(BaseVoter::ANY_ATTRIBUTE, $mapFile);

        $size = $request->query->get('size', ImageServiceInterface::SIZE_FULL);
        $filePath = $imageService->getMapFileSectorFrameImage($mapFile, $size);

        return $this->file($filePath);
    }
}
