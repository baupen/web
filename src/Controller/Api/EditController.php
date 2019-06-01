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

use App\Api\Entity\Edit\UpdateConstructionSite;
use App\Api\Entity\Edit\UpdateCraftsman;
use App\Api\Entity\Edit\UpdateMap;
use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\Edit\CheckMapFileRequest;
use App\Api\Request\Edit\UpdateConstructionSiteRequest;
use App\Api\Request\Edit\UpdateCraftsmanRequest;
use App\Api\Request\Edit\UpdateMapFileRequest;
use App\Api\Request\Edit\UpdateMapRequest;
use App\Api\Request\Edit\UpdateMapSectorsRequest;
use App\Api\Request\Edit\UpdateSectorFrameRequest;
use App\Api\Request\Edit\UploadMapFileRequest;
use App\Api\Response\Data\ConstructionSiteData;
use App\Api\Response\Data\CraftsmanData;
use App\Api\Response\Data\CraftsmenData;
use App\Api\Response\Data\Edit\MapSectorsData;
use App\Api\Response\Data\Edit\SectorFrameData;
use App\Api\Response\Data\Edit\UploadFileCheckData;
use App\Api\Response\Data\EmptyData;
use App\Api\Response\Data\MapData;
use App\Api\Response\Data\MapFileData;
use App\Api\Response\Data\MapFilesData;
use App\Api\Response\Data\MapsData;
use App\Api\Transformer\Edit\ConstructionSiteTransformer;
use App\Api\Transformer\Edit\CraftsmanTransformer;
use App\Api\Transformer\Edit\MapFileTransformer;
use App\Api\Transformer\Edit\MapSectorTransformer;
use App\Api\Transformer\Edit\MapTransformer;
use App\Controller\Api\Base\ApiController;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Map;
use App\Entity\MapFile;
use App\Entity\MapSector;
use App\Service\Interfaces\UploadServiceInterface;
use Exception;
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
    const CONSTRUCTION_SITE_IMAGE_UPLOAD_FAILED = 'construction site image could not be uploaded';
    const MAP_NOT_FOUND = 'map not found';
    const MAP_FILE_ASSIGNED_TO_DIFFERENT_MAP = 'this map file is already assigned to a different map';
    const MAP_FILE_NOT_FOUND = 'file not found';
    const MAP_HAS_ISSUES_ASSIGNED = 'map can not be removed as there are issues assigned to it';
    const CRAFTSMAN_HAS_ISSUES_ASSIGNED = 'craftsman can not be removed as there are issues assigned to it';
    const MAP_HAS_CHILDREN_ASSIGNED = 'map can not be removed as there are children assigned to it';

    /**
     * @Route("/map_file/{mapFile}/sector_frame", name="api_edit_map_file_sector_frame", methods={"GET"})
     *
     * @param Request $request
     * @param MapFile $mapFile
     *
     * @return Response
     */
    public function mapFileFrameAction(Request $request, MapFile $mapFile)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        // deny access if file not from construction site
        if ($constructionSite !== $mapFile->getConstructionSite()) {
            $this->createAccessDeniedException();
        }

        $data = new SectorFrameData();
        $data->setSectorFrame($mapFile->getSectorFrame());

        return $this->success($data);
    }

    /**
     * @Route("/map_file/{mapFile}/frame", name="api_edit_map_file_sector_frame", methods={"POST"})
     *
     * @param Request $request
     * @param MapFile $mapFile
     *
     * @return Response
     */
    public function mapFileFramePostAction(Request $request, MapFile $mapFile)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var UpdateSectorFrameRequest $parsedRequest */
        if (!$this->parseConstructionSiteRequest($request, UpdateSectorFrameRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        // deny access if file not from construction site
        if ($constructionSite !== $mapFile->getConstructionSite()) {
            $this->createAccessDeniedException();
        }

        // save new sector frame
        $mapFile->setSectorFrame($parsedRequest->getSectorFrame());
        $this->fastSave($mapFile);

        return $this->success();
    }

    /**
     * @Route("/map_file/{mapFile}/map_sectors", name="api_edit_map_map_sectors", methods={"GET"})
     *
     * @param Request              $request
     * @param MapFile              $mapFile
     * @param MapSectorTransformer $mapSectorTransformer
     *
     * @return Response
     */
    public function mapFileSectorAction(Request $request, MapFile $mapFile, MapSectorTransformer $mapSectorTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        // deny access if file not from construction site
        if ($constructionSite !== $mapFile->getConstructionSite()) {
            $this->createAccessDeniedException();
        }

        $mapSectors = $mapSectorTransformer->toApiMultiple($mapFile->getSectors());
        $data = new MapSectorsData();
        $data->setMapSectors($mapSectors);

        return $this->success($data);
    }

    /**
     * @Route("/map_file/{mapFile}/map_sectors", name="api_edit_map_map_sectors", methods={"POST"})
     *
     * @param Request $request
     * @param MapFile $mapFile
     *
     * @return Response
     */
    public function mapFileSectorPostAction(Request $request, MapFile $mapFile)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var UpdateMapSectorsRequest $parsedRequest */
        if (!$this->parseConstructionSiteRequest($request, UpdateMapSectorsRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        // deny access if file not from construction site
        if ($constructionSite !== $mapFile->getConstructionSite()) {
            $this->createAccessDeniedException();
        }

        $this->fastRemove(...$mapFile->getSectors()->toArray());

        $newSectors = [];
        foreach ($parsedRequest->getUpdateMapSectors() as $updateMapSector) {
            $newSector = new MapSector();
            $newSector->setIsAutomaticEditEnabled(false);
            $newSector->setMapFile($mapFile);
            $newSector->setIdentifier('manual_' . \count($newSectors));
            $newSector->setName($updateMapSector->getName());
            $newSector->setColor($updateMapSector->getColor());
            $newSector->setPoints($updateMapSector->getPoints());

            $newSectors[] = $newSector;
        }

        $this->fastSave(...$newSectors);

        return $this->success();
    }

    /**
     * @Route("/maps", name="api_edit_maps")
     *
     * @param Request        $request
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
     * @param Request            $request
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
     * @Route("/construction_site", name="api_edit_construction_site")
     *
     * @param Request                     $request
     * @param ConstructionSiteTransformer $constructionSiteTransformer
     *
     * @return Response
     */
    public function constructionSiteAction(Request $request, ConstructionSiteTransformer $constructionSiteTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //create response
        $data = new ConstructionSiteData();
        $data->setConstructionSite($constructionSiteTransformer->toApi($constructionSite));

        return $this->success($data);
    }

    /**
     * @Route("/craftsmen", name="api_edit_craftsmen")
     *
     * @param Request              $request
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
     * @param Request                $request
     * @param UploadServiceInterface $uploadService
     *
     * @throws Exception
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
     * @param Request                $request
     * @param MapFileTransformer     $mapFileTransformer
     * @param UploadServiceInterface $uploadService
     *
     * @throws Exception
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
     * @Route("/construction_site/image", name="api_edit_construction_site_image", methods={"POST"})
     *
     * @param Request                $request
     * @param UploadServiceInterface $uploadService
     *
     * @throws Exception
     *
     * @return Response
     */
    public function constructionSiteImageAction(Request $request, UploadServiceInterface $uploadService)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var ConstructionSiteRequest $parsedRequest */
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
        $constructionSiteImage = $uploadService->uploadConstructionSiteImage($file, $constructionSite, $file->getClientOriginalName());
        if ($constructionSiteImage === null) {
            return $this->fail(self::CONSTRUCTION_SITE_IMAGE_UPLOAD_FAILED);
        }
        $constructionSiteImage->setConstructionSite($constructionSite);
        $constructionSite->getImages()->add($constructionSiteImage);
        $constructionSite->setImage($constructionSiteImage);
        $this->fastSave($constructionSite, $constructionSiteImage);

        //create response
        return $this->success();
    }

    /**
     * @Route("/map_file/{mapFile}", name="api_edit_map_file_put", methods={"PUT"})
     *
     * @param Request            $request
     * @param MapFile            $mapFile
     * @param MapFileTransformer $mapFileTransformer
     *
     * @throws Exception
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
     * @param Request        $request
     * @param MapTransformer $mapTransformer
     *
     * @throws Exception
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
     * @Route("/construction_site/save", name="api_edit_construction_site_save", methods={"PUT"})
     *
     * @param Request $request
     *
     * @throws Exception
     *
     * @return Response
     */
    public function constructionSiteSaveAction(Request $request)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var UpdateConstructionSiteRequest $parsedRequest */
        if (!$this->parseConstructionSiteRequest($request, UpdateConstructionSiteRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $updateConstructionSite = $parsedRequest->getConstructionSite();
        if (!$this->writeIntoConstructionSiteEntity($updateConstructionSite, $constructionSite)) {
            return $errorResponse;
        }

        $this->fastSave($constructionSite);

        //create response
        return $this->success();
    }

    /**
     * @Route("/map/{map}", name="api_edit_map_put", methods={"PUT"})
     *
     * @param Request        $request
     * @param Map            $map
     * @param MapTransformer $mapTransformer
     *
     * @throws Exception
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
     * @param Map     $map
     *
     * @throws Exception
     *
     * @return Response
     */
    public function mapDeleteAction(Request $request, Map $map)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
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

        $manager = $this->getDoctrine()->getManager();
        foreach ($map->getFiles() as $file) {
            $file->setMap(null);
            $manager->persist($file);
        }

        $manager->remove($map);
        $manager->flush();

        //create response
        return $this->success(new EmptyData());
    }

    /**
     * @Route("/craftsman", name="api_edit_craftsman_post", methods={"POST"})
     *
     * @param Request              $request
     * @param CraftsmanTransformer $craftsmanTransformer
     *
     * @throws Exception
     *
     * @return Response
     */
    public function craftsmanPostAction(Request $request, CraftsmanTransformer $craftsmanTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var UpdateCraftsmanRequest $parsedRequest */
        if (!$this->parseConstructionSiteRequest($request, UpdateCraftsmanRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $updateCraftsman = $parsedRequest->getCraftsman();
        $craftsman = new Craftsman();
        $craftsman->setConstructionSite($constructionSite);
        $craftsman->setEmailIdentifier();

        if (!$this->writeIntoCraftsmanEntity($updateCraftsman, $craftsman)) {
            return $errorResponse;
        }

        $this->fastSave($craftsman);

        //create response
        $data = new CraftsmanData();
        $data->setCraftsman($craftsmanTransformer->toApi($craftsman));

        return $this->success($data);
    }

    /**
     * @Route("/craftsman/{craftsman}", name="api_edit_craftsman_put", methods={"PUT"})
     *
     * @param Request              $request
     * @param Craftsman            $craftsman
     * @param CraftsmanTransformer $craftsmanTransformer
     *
     * @throws Exception
     *
     * @return Response
     */
    public function craftsmanPutAction(Request $request, Craftsman $craftsman, CraftsmanTransformer $craftsmanTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        /** @var UpdateCraftsmanRequest $parsedRequest */
        if (!$this->parseConstructionSiteRequest($request, UpdateCraftsmanRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        if (!$constructionSite->getCraftsmen()->contains($craftsman)) {
            throw new NotFoundHttpException();
        }

        $updateCraftsman = $parsedRequest->getCraftsman();

        if (!$this->writeIntoCraftsmanEntity($updateCraftsman, $craftsman)) {
            return $errorResponse;
        }

        $this->fastSave($craftsman);

        //create response
        $data = new CraftsmanData();
        $data->setCraftsman($craftsmanTransformer->toApi($craftsman));

        return $this->success($data);
    }

    /**
     * @Route("/craftsman/{craftsman}", name="api_edit_craftsman_delete", methods={"DELETE"})
     *
     * @param Request   $request
     * @param Craftsman $craftsman
     *
     * @throws Exception
     *
     * @return Response
     */
    public function craftsmanDeleteAction(Request $request, Craftsman $craftsman)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $parsedRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        if (!$constructionSite->getCraftsmen()->contains($craftsman)) {
            throw new NotFoundHttpException();
        }

        if ($craftsman->getIssues()->count() !== 0) {
            return $this->fail(self::CRAFTSMAN_HAS_ISSUES_ASSIGNED);
        }

        $this->fastRemove($craftsman);

        //create response
        return $this->success(new EmptyData());
    }

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
     * @param UpdateMap $updateMap
     * @param Map       $entity
     * @param $errorResponse
     *
     * @return bool
     */
    private function writeIntoMapEntity(UpdateMap $updateMap, Map $entity, &$errorResponse)
    {
        $entity->setName($updateMap->getName());

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

            if ($entity->getFile() !== null) {
                $this->copySectorInformation($entity->getFile(), $file);
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

    /**
     * @param UpdateConstructionSite $updateConstructionSite
     * @param ConstructionSite       $entity
     *
     * @return bool
     */
    private function writeIntoConstructionSiteEntity(UpdateConstructionSite $updateConstructionSite, ConstructionSite $entity)
    {
        $entity->setStreetAddress($updateConstructionSite->getStreetAddress());
        $entity->setPostalCode($updateConstructionSite->getPostalCode());
        $entity->setLocality($updateConstructionSite->getLocality());

        return true;
    }

    /**
     * @param UpdateCraftsman $updateCraftsman
     * @param Craftsman       $entity
     *
     * @return bool
     */
    private function writeIntoCraftsmanEntity(UpdateCraftsman $updateCraftsman, Craftsman $entity)
    {
        $entity->setContactName($updateCraftsman->getContactName());
        $entity->setCompany($updateCraftsman->getCompany());
        $entity->setEmail($updateCraftsman->getEmail());
        $entity->setTrade($updateCraftsman->getTrade());

        return true;
    }

    /**
     * @param MapFile $source
     * @param $target
     */
    private function copySectorInformation(MapFile $source, MapFile $target): void
    {
        // transfer sector frame
        if ($target->getSectorFrame() === null) {
            $target->setSectorFrame($source->getSectorFrame());
        }

        // transfer sectors
        if (\count($target->getSectors()) === 0) {
            foreach ($source->getSectors() as $sector) {
                $newSector = new MapSector();
                $newSector->setPoints($sector->getPoints());
                $newSector->setColor($sector->getColor());
                $newSector->setName($sector->getName());
                $newSector->setIdentifier($sector->getIdentifier());
                $newSector->setMapFile($target);
                $newSector->setIsAutomaticEditEnabled(false);

                $target->getSectors()->add($newSector);
            }
        }
    }
}
