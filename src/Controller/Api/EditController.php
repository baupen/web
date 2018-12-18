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

use App\Api\Entity\Edit\UpdateMap;
use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\Edit\UpdateMapRequest;
use App\Api\Response\Data\MapData;
use App\Api\Response\Data\MapFileData;
use App\Api\Response\Data\MapFilesData;
use App\Api\Response\Data\MapsData;
use App\Api\Transformer\Edit\MapFileTransformer;
use App\Api\Transformer\Edit\MapTransformer;
use App\Controller\Api\Base\ApiController;
use App\Entity\ConstructionSite;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Service\Interfaces\UploadServiceInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/edit")
 */
class EditController extends ApiController
{
    const INCORRECT_NUMBER_OF_FILES = 'incorrect number of files';
    const MAP_FILE_UPLOAD_FAILED = 'map file could not be uploaded';
    const MAP_NOT_FOUND = 'map not found';
    const MAP_FILE_NOT_FOUND = 'file not found';

    /**
     * gives the appropriate error code the specified error message.
     *
     * @param string $message
     *
     * @return int
     */
    protected function errorMessageToStatusCode($message)
    {
        return parent::errorMessageToStatusCode($message);
    }

    /**
     * @Route("/maps", name="api_edit_maps")
     *
     * @param Request $request
     * @param MapTransformer $mapFileTransformer
     *
     * @return Response
     */
    public function mapsAction(Request $request, MapTransformer $mapFileTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $mapFiles = $this->getDoctrine()->getRepository(Map::class)->findBy(['constructionSite' => $constructionSite->getId()]);

        //create response
        $data = new MapsData();
        $data->setMaps($mapFileTransformer->toApiMultiple($mapFiles));

        return $this->success($data);
    }

    /**
     * @Route("/map_files", name="api_edit_map_files")
     *
     * @param Request $request
     * @param MapFileTransformer $mapFileTransformer
     *
     * @return Response
     */
    public function mapFilesAction(Request $request, MapFileTransformer $mapFileTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $mapFiles = $this->getDoctrine()->getRepository(MapFile::class)->findBy(['constructionSite' => $constructionSite->getId()]);

        //create response
        $data = new MapFilesData();
        $data->setMapFiles($mapFileTransformer->toApiMultiple($mapFiles));

        return $this->success($data);
    }

    /**
     * @Route("/map_files/upload", name="api_edit_map_files_upload", methods={"POST"})
     *
     * @param Request $request
     * @param MapFileTransformer $mapFileTransformer
     * @param UploadServiceInterface $uploadService
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function mapFileUploadAction(Request $request, MapFileTransformer $mapFileTransformer, UploadServiceInterface $uploadService)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //check if file is here
        if ($request->files->count() !== 1) {
            return $this->fail(self::INCORRECT_NUMBER_OF_FILES);
        }

        /** @var UploadedFile $file */
        $file = $request->files->getIterator()->current();

        //save file
        $mapFile = $uploadService->uploadMapFile($file, $constructionSite);
        if ($mapFile === null) {
            return $this->fail(self::MAP_FILE_UPLOAD_FAILED);
        }
        $mapFile->setConstructionSite($constructionSite);
        $this->fastSave($mapFile);

        //create response
        $data = new MapFileData();
        $data->setMapFile($mapFileTransformer->toApi($mapFile));

        return $this->success($data);
    }

    /**
     * @Route("/map", name="api_edit_map", methods={"POST"})
     *
     * @param Request $request
     * @param MapFileTransformer $mapFileTransformer
     * @param UploadServiceInterface $uploadService
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function mapFPostAction(Request $request, MapTransformer $mapTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var UpdateMapRequest $parsedRequest */
        if (!$this->parseConstructionSiteRequest($request, UpdateMapRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $updateMap = $parsedRequest->getMap();
        $map = new Map();
        $map->setConstructionSite($constructionSite);

        if (!$this->writeIntoEntity($updateMap, $map, $errorResponse)) {
            return $errorResponse;
        }

        $this->fastSave($map);

        //create response
        $data = new MapData();
        $data->setMap($mapTransformer->toApi($map));

        return $this->success($data);
    }

    /**
     * @param UpdateMap $updateMap
     * @param Map $entity
     * @param $errorResponse
     *
     * @return bool
     */
    private function writeIntoEntity(UpdateMap $updateMap, Map $entity, &$errorResponse)
    {
        $entity->setName($updateMap->getName());
        $entity->setIsAutomaticEditEnabled($updateMap->getIsAutomaticEditEnabled());

        if ($updateMap->getFileId() !== null) {
            $file = $this->getDoctrine()->getRepository(MapFile::class)->find($updateMap->getFileId());
            if ($file === null || $file->getConstructionSite() !== $entity->getConstructionSite()) {
                $errorResponse = $this->fail(self::MAP_FILE_NOT_FOUND);

                return false;
            }

            $entity->setFile($file);
        }

        if ($updateMap->getParentId() !== null) {
            $parentMap = $this->getDoctrine()->getRepository(Map::class)->find($updateMap->getParentId());
            if ($parentMap === null || $parentMap->getConstructionSite() !== $entity->getConstructionSite()) {
                $errorResponse = $this->fail(self::MAP_NOT_FOUND);

                return false;
            }

            $entity->setParent($parentMap);
        }

        return true;
    }
}
