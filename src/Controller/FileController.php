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
use App\Entity\Map;
use App\Entity\MapFile;
use App\Security\Voter\MapVoter;
use App\Service\Interfaces\CacheServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\StorageServiceInterface;
use App\Service\MapFileService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends BaseDoctrineController
{
    /**
     * @Route("/maps/{map}/file/{mapFile}/{filename}", name="map_file", methods={"GET"})
     *
     * @return Response
     */
    public function getMapFileAction(Request $request, Map $map, MapFile $mapFile, string $filename, PathServiceInterface $pathService, MapFileService $mapFileService)
    {
        $this->denyAccessUnlessGranted(MapVoter::MAP_VIEW, $map);
        if ($map->getFile() !== $mapFile || $mapFile->getFilename() !== $filename) {
            throw new NotFoundHttpException();
        }

        $sanitizedVariant = strtolower($request->query->get('variant', ''));
        if ('' !== $sanitizedVariant && 'ios' !== $sanitizedVariant) {
            throw new NotFoundHttpException();
        }

        $path = $pathService->getFolderForMapFiles($mapFile->getConstructionSite()).\DIRECTORY_SEPARATOR.$mapFile->getFilename();

        if ('ios' === $sanitizedVariant) {
            $optimized = $mapFileService->renderForMobileDevice($mapFile);
            if (null !== $optimized) {
                $path = $optimized;
            }
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $mapFile->getFilename()
        );

        return $response;
    }

    /**
     * @Route("/maps/{map}/file", name="post_map_file", methods={"POST"})
     *
     * @return Response
     */
    public function postMapFile(Request $request, Map $map, StorageServiceInterface $storageService, CacheServiceInterface $cacheService)
    {
        $this->denyAccessUnlessGranted(MapVoter::MAP_MODIFY, $map);
        if (1 !== $request->files->count()) {
            throw new BadRequestException();
        }

        $files = $request->files->all();
        $file = $files[array_key_first($files)];
        $mapFile = $storageService->uploadMapFile($file, $map);
        if (null === $mapFile) {
            throw new BadRequestException();
        }

        $this->fastSave($map, $mapFile);
        $cacheService->warmUpCacheForMapFile($mapFile);

        $url = $this->generateUrl('map_file', ['map' => $map->getId(), 'mapFile' => $mapFile->getId(), 'filename' => $mapFile->getFilename()]);

        return new Response($url, Response::HTTP_CREATED);
    }
}
