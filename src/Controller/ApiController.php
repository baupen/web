<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;


use App\Api\ApiSerializable;
use App\Api\Request\Base\BaseRequest;
use App\Api\Request\DownloadFileRequest;
use App\Api\Request\LoginRequest;
use App\Api\Response\Base\BaseResponse;
use App\Api\Response\LoginResponse;
use App\Api\Request\SyncRequest;
use App\Api\Response\SyncResponse;
use App\Controller\Base\BaseDoctrineController;
use App\Controller\Base\BaseFormController;
use App\Entity\AppUser;
use App\Entity\Building;
use App\Entity\BuildingMap;
use App\Entity\Craftsman;
use App\Entity\FrontendUser;
use App\Entity\Marker;
use App\Entity\Traits\IdTrait;
use App\Enum\ApiStatus;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/api")
 *
 * @return Response
 */
class ApiController extends BaseDoctrineController
{

    /**
     * inject the translator service
     *
     * @return array
     */
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + ['translator' => TranslatorInterface::class];
    }

    /**
     * @Route("/login", name="api_login")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function loginAction(Request $request, SerializerInterface $serializer)
    {
        if (!($content = $request->getContent())) {
            return $this->failed(ApiStatus::EMPTY_REQUEST);
        }

        /* @var LoginRequest $loginRequest */
        $loginRequest = $serializer->deserialize($content, LoginRequest::class, "json");

        $user = $this->getDoctrine()->getRepository(AppUser::class)->findOneBy(["identifier" => $loginRequest->getIdentifier()]);
        if ($user === null) {
            return $this->failed(ApiStatus::UNKNOWN_IDENTIFIER);
        }
        if ($user->getPasswordHash() !== $loginRequest->getPasswordHash()) {
            return $this->failed(ApiStatus::WRONG_PASSWORD);
        }

        $user->setAuthenticationToken();
        $this->fastSave($user);

        $loginResponse = new LoginResponse();
        $loginResponse->setUser($user);
        return $this->json($loginResponse);
    }

    /**
     * @Route("/authentication_status", name="api_authentication_status")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function authenticationStatusAction(Request $request, SerializerInterface $serializer)
    {
        if (!($content = $request->getContent())) {
            return $this->failed(ApiStatus::EMPTY_REQUEST);
        }

        /* @var BaseRequest $authenticationStatusRequest */
        $authenticationStatusRequest = $serializer->deserialize($content, BaseRequest::class, "json");

        $user = $this->getDoctrine()->getRepository(AppUser::class)->findOneBy(["authenticationToken" => $authenticationStatusRequest->getAuthenticationToken()]);
        if ($user === null) {
            return $this->failed(ApiStatus::INVALID_AUTHENTICATION_TOKEN);
        }

        return $this->json(new BaseResponse());
    }

    /**
     * @Route("/file/upload", name="api_file_upload")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function fileUploadAction(Request $request, SerializerInterface $serializer)
    {
        if (!($content = $request->getContent())) {
            return $this->failed(ApiStatus::EMPTY_REQUEST);
        }

        /* @var BaseRequest $authenticationStatusRequest */
        $authenticationStatusRequest = $serializer->deserialize($content, BaseRequest::class, "json");

        $user = $this->getDoctrine()->getRepository(AppUser::class)->findOneBy(["authenticationToken" => $authenticationStatusRequest->getAuthenticationToken()]);
        if ($user === null) {
            return $this->failed(ApiStatus::INVALID_AUTHENTICATION_TOKEN);
        }

        foreach ($request->files->all() as $key => $file) {
            /* @var UploadedFile $file */
            if (!$file->move($this->getParameter("PUBLIC_DIR") . "/upload")) {
                return $this->failed(ApiStatus::INVALID_FILE);
            }
            $marker = $this->getDoctrine()->getRepository(Marker::class)->find($key);
            $marker->setImageFileName($file->getFilename());
            $this->fastSave($marker);
        }

        return $this->json(new BaseResponse());
    }

    /**
     * @Route("/file/download", name="api_file_download")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function fileDownloadAction(Request $request, SerializerInterface $serializer)
    {
        if (!($content = $request->getContent())) {
            return $this->failed(ApiStatus::EMPTY_REQUEST);
        }

        /* @var DownloadFileRequest $downloadFileRequest */
        $downloadFileRequest = $serializer->deserialize($content, DownloadFileRequest::class, "json");

        $user = $this->getDoctrine()->getRepository(AppUser::class)->findOneBy(["authenticationToken" => $downloadFileRequest->getAuthenticationToken()]);
        if ($user === null) {
            return $this->failed(ApiStatus::INVALID_AUTHENTICATION_TOKEN);
        }

        $marker = $this->getDoctrine()->getRepository(Marker::class)->findOneBy(["imageFileName" => $downloadFileRequest->getFileName()]);
        if ($marker === null) {
            return $this->failed(ApiStatus::INVALID_FILE);
        }

        return $this->file($this->getParameter("PUBLIC_DIR") . "/upload/" . $marker->getImageFileName());
    }

    /**
     * @Route("/sync", name="api_sync")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function syncAction(Request $request, SerializerInterface $serializer)
    {
        if (!($content = $request->getContent())) {
            return $this->failed(ApiStatus::EMPTY_REQUEST);
        }

        /* @var SyncRequest $syncRequest */
        $syncRequest = $serializer->deserialize($content, SyncRequest::class, "json");

        $user = $this->getDoctrine()->getRepository(AppUser::class)->findOneBy(["authenticationToken" => $syncRequest->getAuthenticationToken()]);
        if ($user === null) {
            return $this->failed(ApiStatus::INVALID_AUTHENTICATION_TOKEN);
        }

        //replace entities
        if (is_array($syncRequest->getMarkers())) {
            foreach ($syncRequest->getMarkers() as $marker) {
                //replace guid with objects
                $buildingMap = $this->getDoctrine()->getRepository(BuildingMap::class)->find($marker["buildingMap"]);
                $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->find($marker["craftsman"]);

                //unset properties which are not of the correct type
                unset($marker["buildingMap"]);
                unset($marker["craftsman"]);

                //if id forgotten; add one here
                if (!isset($marker["id"])) {
                    $marker["id"] = strtoupper(Uuid::uuid4());
                }

                /* @var Marker $markerEntity */
                $markerEntity = $serializer->deserialize(json_encode((object)$marker), Marker::class, "json");
                $markerEntity->setCreatedBy($user);
                $markerEntity->setBuildingMap($buildingMap);
                $markerEntity->setCraftsman($craftsman);
                $this->fastSave($markerEntity);
            }
        }

        $syncResponse = new SyncResponse();
        $syncResponse->setUser($user);
        $syncResponse->setBuildings($this->getDoctrine()->getRepository(Building::class)->findByAppUser($user));
        $syncResponse->setCraftsmen($this->getDoctrine()->getRepository(Craftsman::class)->findAll());

        $maps = [];
        $syncResponse->setBuildingMaps([]);
        foreach ($syncResponse->getBuildings() as $building) {
            $maps = array_merge($building->getBuildingMaps()->toArray(), $syncResponse->getBuildingMaps());
        }
        $syncResponse->setBuildingMaps($maps);

        $markers = [];
        $syncResponse->setMarkers([]);
        foreach ($syncResponse->getBuildingMaps() as $buildingMap) {
            $markers = array_merge($buildingMap->getMarkers()->toArray(), $syncResponse->getMarkers());
        }
        $syncResponse->setMarkers($markers);

        return $this->json($syncResponse);
    }

    /**
     * @param ApiStatus|int $apiError
     * @return JsonResponse
     */
    private function failed($apiError)
    {
        $response = new BaseResponse();
        $response->setApiStatus($apiError);
        $response->setApiErrorMessage(ApiStatus::getTranslationForValue($apiError, $this->get("translator")));

        return $this->json($response);
    }

    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * @final
     * @param $data
     * @param int $status
     * @param array $headers
     * @param array $context
     * @return JsonResponse
     */
    protected function json($data, int $status = 200, array $headers = array(), array $context = array()): JsonResponse
    {
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceLimit(0);
        $normalizer->setCircularReferenceHandler(function ($object) {
            /* @var IdTrait $object */
            return $object->getId();
        });

        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        if ($data instanceof BaseResponse) {
            $data->prepareSerialization();

        }

        $json = $serializer->serialize($data, 'json', array_merge(array(
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ), $context));

        return new JsonResponse($json, $status, $headers, true);
    }
}
