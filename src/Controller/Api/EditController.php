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
use App\Api\Request\Edit\CheckMapFileRequest;
use App\Api\Request\Edit\UpdateMapFileRequest;
use App\Api\Request\Edit\UpdateMapRequest;
use App\Api\Request\Edit\UploadMapFileRequest;
use App\Api\Response\Data\CraftsmenData;
use App\Api\Response\Data\Edit\UploadFileCheckData;
use App\Api\Response\Data\EmptyData;
use App\Api\Response\Data\MapData;
use App\Api\Response\Data\MapFileData;
use App\Api\Response\Data\MapFilesData;
use App\Api\Response\Data\MapsData;
use App\Api\Transformer\Edit\CraftsmanTransformer;
use App\Api\Transformer\Edit\MapFileTransformer;
use App\Api\Transformer\Edit\MapTransformer;
use App\Controller\Api\Base\ApiController;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Service\Interfaces\UploadServiceInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/edit")
 */
class EditController extends ApiController
{
    const INCORRECT_NUMBER_OF_FILES = 'incorrect number of files';
    const MAP_FILE_UPLOAD_FAILED = 'map file could not be uploaded';
    const MAP_NOT_FOUND = 'map not found';
    const MAP_FILE_ASSIGNED_TO_DIFFERENT_MAP = 'this map file is already assigned to a different map';
    const MAP_FILE_NOT_FOUND = 'file not found';
    const MAP_HAS_ISSUES_ASSIGNED = 'map can not be removed as there are issues assigned to it';
    const MAP_HAS_CHILDREN_ASSIGNED = 'map can not be removed as there are children assigned to it';

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
     * @Route("/craftsmen", name="api_edit_craftsmen")
     *
     * @param Request $request
     * @param CraftsmanTransformer $craftsmanTransformer
     *
     * @return Response
     */
    public function craftsmenAction(Request $request, CraftsmanTransformer $craftsmanTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $craftsmen = $this->getDoctrine()->getRepository(Craftsman::class)->findBy(['constructionSite' => $constructionSite->getId()]);

        //create response
        $data = new CraftsmenData();
        $data->setCraftsmen($craftsmanTransformer->toApiMultiple($craftsmen));

        return $this->success($data);
    }

    /**
     * @Route("/map_file/check", name="api_edit_map_file_check", methods={"POST"})
     *
     * @param Request $request
     * @param UploadServiceInterface $uploadService
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function mapFileCheckPostAction(Request $request, UploadServiceInterface $uploadService)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var CheckMapFileRequest $parsedRequest */
        if (!$this->parseConstructionSiteRequest($request, CheckMapFileRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $mapFile = $parsedRequest->getMapFile();
        $checkResult = $uploadService->checkUploadMapFile($mapFile->getHash(), $mapFile->getFilename(), $constructionSite);

        //create response
        $data = new UploadFileCheckData();
        $data->setUploadFileCheck($checkResult);

        return $this->success($data);
    }

    /**
     * @Route("/map_file", name="api_edit_map_file_post", methods={"POST"})
     *
     * @param Request $request
     * @param MapFileTransformer $mapFileTransformer
     * @param UploadServiceInterface $uploadService
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function mapFilePostAction(Request $request, MapFileTransformer $mapFileTransformer, UploadServiceInterface $uploadService)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var UploadMapFileRequest $parsedRequest */
        if (!$this->parseConstructionSiteRequest($request, UploadMapFileRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //check if file is here
        if ($request->files->count() !== 1) {
            return $this->fail(self::INCORRECT_NUMBER_OF_FILES);
        }

        /** @var UploadedFile $file */
        $file = $request->files->getIterator()->current();

        //save file
        $mapFile = $uploadService->uploadMapFile($file, $constructionSite, $parsedRequest->getMapFile()->getFilename());
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
     * @Route("/map_file/{mapFile}", name="api_edit_map_file_put", methods={"PUT"})
     *
     * @param Request $request
     * @param MapFile $mapFile
     * @param MapFileTransformer $mapFileTransformer
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function mapFilePutAction(Request $request, MapFile $mapFile, MapFileTransformer $mapFileTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var UpdateMapFileRequest $parsedRequest */
        if (!$this->parseConstructionSiteRequest($request, UpdateMapFileRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $map = $this->getDoctrine()->getRepository(Map::class)->findOneBy(['constructionSite' => $constructionSite->getId(), 'id' => $parsedRequest->getMapFile()->getMapId()]);
        if ($map === null) {
            return $this->fail(self::MAP_NOT_FOUND);
        }

        $mapFile->setMap($map);
        $this->fastSave($mapFile);

        //create response
        $data = new MapFileData();
        $data->setMapFile($mapFileTransformer->toApi($mapFile));

        return $this->success($data);
    }

    /**
     * @Route("/map", name="api_edit_map_post", methods={"POST"})
     *
     * @param Request $request
     * @param MapTransformer $mapTransformer
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function mapPostAction(Request $request, MapTransformer $mapTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var UpdateMapRequest $parsedRequest */
        if (!$this->parseConstructionSiteRequest($request, UpdateMapRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $updateMap = $parsedRequest->getMap();
        $map = new Map();
        $map->setConstructionSite($constructionSite);

        if (!$this->writeIntoMapEntity($updateMap, $map, $errorResponse)) {
            return $errorResponse;
        }

        $this->fastSave($map);

        //create response
        $data = new MapData();
        $data->setMap($mapTransformer->toApi($map));

        return $this->success($data);
    }

    /**
     * @Route("/map/{map}", name="api_edit_map_put", methods={"PUT"})
     *
     * @param Request $request
     * @param Map $map
     * @param MapTransformer $mapTransformer
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function mapPutAction(Request $request, Map $map, MapTransformer $mapTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var UpdateMapRequest $parsedRequest */
        if (!$this->parseConstructionSiteRequest($request, UpdateMapRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        if (!$constructionSite->getMaps()->contains($map)) {
            throw new NotFoundHttpException();
        }

        $updateMap = $parsedRequest->getMap();

        if (!$this->writeIntoMapEntity($updateMap, $map, $errorResponse)) {
            return $errorResponse;
        }

        $this->fastSave($map);

        //create response
        $data = new MapData();
        $data->setMap($mapTransformer->toApi($map));

        return $this->success($data);
    }

    /**
     * @Route("/map/{map}", name="api_edit_map_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param Map $map
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function mapDeleteAction(Request $request, Map $map)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, UpdateMapRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        if (!$constructionSite->getMaps()->contains($map)) {
            throw new NotFoundHttpException();
        }

        if ($map->getIssues()->count() !== 0) {
            return $this->fail(self::MAP_HAS_ISSUES_ASSIGNED);
        }

        if ($map->getChildren()->count() !== 0) {
            return $this->fail(self::MAP_HAS_CHILDREN_ASSIGNED);
        }

        $this->fastRemove($map);

        //create response
        return $this->success(new EmptyData());
    }

    /**
     * @param UpdateMap $updateMap
     * @param Map $entity
     * @param $errorResponse
     *
     * @return bool
     */
    private function writeIntoMapEntity(UpdateMap $updateMap, Map $entity, &$errorResponse)
    {
        $entity->setName($updateMap->getName());
        $entity->setIsAutomaticEditEnabled($updateMap->getIsAutomaticEditEnabled());

        if ($updateMap->getFileId() !== null) {
            $file = $this->getDoctrine()->getRepository(MapFile::class)->find($updateMap->getFileId());
            if ($file === null || $file->getConstructionSite() !== $entity->getConstructionSite()) {
                $errorResponse = $this->fail(self::MAP_FILE_NOT_FOUND);

                return false;
            }

            if ($file->getMap() !== $entity) {
                $errorResponse = $this->fail(self::MAP_FILE_ASSIGNED_TO_DIFFERENT_MAP);

                return false;
            }

            $entity->setFile($file);
        } else {
            $entity->setFile(null);
        }

        if ($updateMap->getParentId() !== null) {
            $parentMap = $this->getDoctrine()->getRepository(Map::class)->find($updateMap->getParentId());
            if ($parentMap === null || $parentMap->getConstructionSite() !== $entity->getConstructionSite()) {
                $errorResponse = $this->fail(self::MAP_NOT_FOUND);

                return false;
            }

            $entity->setParent($parentMap);
        } else {
            $entity->setParent(null);
        }

        return true;
    }
}
