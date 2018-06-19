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


use App\Api\Request\Base\AbstractRequest;
use App\Api\Request\DownloadFileRequest;
use App\Api\Request\LoginRequest;
use App\Api\Request\ReadRequest;
use App\Api\Response\Base\AbstractResponse;
use App\Api\Response\LoginContent;
use App\Api\Response\SyncResponse;
use App\Controller\Base\BaseDoctrineController;
use App\Entity\ConstructionManager;
use App\Entity\Building;
use App\Entity\Map;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Entity\Traits\IdTrait;
use App\Enum\ApiStatus;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
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

        $user = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(["identifier" => $loginRequest->getUsername()]);
        if ($user === null) {
            return $this->failed(ApiStatus::UNKNOWN_IDENTIFIER);
        }
        if ($user->getPasswordHash() !== $loginRequest->getPasswordHash()) {
            return $this->failed(ApiStatus::WRONG_PASSWORD);
        }

        $user->setAuthenticationToken();
        $this->fastSave($user);

        $loginResponse = new LoginContent();
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

        /* @var AbstractRequest $authenticationStatusRequest */
        $authenticationStatusRequest = $serializer->deserialize($content, AbstractRequest::class, "json");

        $user = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(["authenticationToken" => $authenticationStatusRequest->getAuthenticationToken()]);
        if ($user === null) {
            return $this->failed(ApiStatus::INVALID_AUTHENTICATION_TOKEN);
        }

        return $this->json(new AbstractResponse());
    }

    /**
     * @Route("/file/upload", name="api_file_upload")
     *
     * @param Request $request
     * @return Response
     */
    public function fileUploadAction(Request $request)
    {
        if (!$request->headers->has("mangel-authentication-token")) {
            return $this->failed(ApiStatus::MISSING_AUTHENTICATION_TOKEN);
        }

        $authenticationToken = $request->headers->get("mangel-authentication-token");

        $user = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(["authenticationToken" => $authenticationToken]);
        if ($user === null) {
            return $this->failed(ApiStatus::INVALID_AUTHENTICATION_TOKEN);
        }

        if (count($request->files->all()) == 0) {
            return $this->failed(ApiStatus::NO_FILES);
        }

        foreach ($request->files->all() as $key => $file) {
            /* @var UploadedFile $file */
            if (!$file->move($this->getParameter("PUBLIC_DIR") . "/upload", $file->getClientOriginalName())) {
                return $this->failed(ApiStatus::INVALID_FILE);
            }
        }

        return $this->json(new AbstractResponse());
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

        $user = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(["authenticationToken" => $downloadFileRequest->getAuthenticationToken()]);
        if ($user === null) {
            return $this->failed(ApiStatus::INVALID_AUTHENTICATION_TOKEN);
        }

        $marker = $this->getDoctrine()->getRepository(Issue::class)->findOneBy(["imageFileName" => $downloadFileRequest->getFileName()]);
        if ($marker === null) {
            $buildingMap = $this->getDoctrine()->getRepository(Map::class)->findOneBy(["fileName" => $downloadFileRequest->getFileName()]);
            if ($buildingMap === null)
                return $this->failed(ApiStatus::INVALID_FILE);
        }

        return $this->file($this->getParameter("PUBLIC_DIR") . "/upload/" . $downloadFileRequest->getFileName());
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

        /* @var ReadRequest $syncRequest */
        $syncRequest = $serializer->deserialize($content, ReadRequest::class, "json");

        $user = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(["authenticationToken" => $syncRequest->getAuthenticationToken()]);
        if ($user === null) {
            return $this->failed(ApiStatus::INVALID_AUTHENTICATION_TOKEN);
        }

        //replace entities
        if (is_array($syncRequest->getMarkers())) {
            foreach ($syncRequest->getMarkers() as $marker) {
                //replace guid with objects
                $buildingMap = $this->getDoctrine()->getRepository(Map::class)->find($marker["buildingMap"]);
                $craftsman = $this->getDoctrine()->getRepository(Craftsman::class)->find($marker["craftsman"]);

                //unset properties which are not of the correct type
                unset($marker["buildingMap"]);
                unset($marker["craftsman"]);

                $markerEntity = null;
                if (isset($marker["id"])) {
                    $markerEntity = $this->getDoctrine()->getRepository(Issue::class)->findOneBy(["id" => $marker["id"]]);
                }

                if ($markerEntity == null) {
                    /* @var Issue $markerEntity */
                    $markerEntity = new Issue();
                }

                $markerEntity->setContent($marker["content"]);
                $markerEntity->setImageFileName($marker["imageFileName"]);
                if ($marker["approved"] != null) {
                    $markerEntity->setApproved(new \DateTime($marker["approved"]));
                } else {
                    $markerEntity->setApproved(null);
                }
                $markerEntity->setMarkXPercentage($marker["markXPercentage"]);
                $markerEntity->setMarkYPercentage($marker["markYPercentage"]);
                $markerEntity->setFrameXPercentage($marker["frameXPercentage"]);
                $markerEntity->setFrameYPercentage($marker["frameYPercentage"]);
                $markerEntity->setFrameXHeight($marker["frameXHeight"]);
                $markerEntity->setFrameYLength($marker["frameYLength"]);
                $markerEntity->setCraftsman($craftsman);
                $markerEntity->setCreatedBy($user);
                $markerEntity->setBuildingMap($buildingMap);
                $markerEntity->setCreatedBy($user);
                $markerEntity->setBuildingMap($buildingMap);
                $markerEntity->setCraftsman($craftsman);
                $this->fastSave($markerEntity);
            }
        }

        $syncResponse = new SyncResponse();
        $syncResponse->setUser($user);
        $syncResponse->setBuildings($this->getDoctrine()->getRepository(Building::class)->findByAppUser($user));
        $syncResponse->setCraftsmen($this->getDoctrine()->getRepository(Craftsman::class)->findBy([], ["name" => "ASC"]));

        $maps = [];
        $syncResponse->setBuildingMaps([]);
        foreach ($syncResponse->getBuildings() as $building) {
            $maps = array_merge($building->getMaps()->toArray(), $maps);
        }
        $syncResponse->setBuildingMaps($maps);

        $markers = [];
        $syncResponse->setMarkers([]);
        foreach ($syncResponse->getBuildingMaps() as $buildingMap) {
            $markers = array_merge($buildingMap->getMarkers()->toArray(), $markers);
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
        $response = new AbstractResponse();
        $response->setApiStatus($apiError);
        $response->setApiErrorMessage(ApiStatus::getTranslationForValue($apiError, $this->get("translator")));


        return $this->json($response);
    }

    /**
     * @return Serializer
     */
    public static function getSerializer()
    {
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceLimit(0);
        $normalizer->setCircularReferenceHandler(function ($object) {
            /* @var IdTrait $object */
            return $object->getId();
        });

        return new Serializer([new DateTimeNormalizer(), $normalizer], [new JsonEncoder()]);
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
        $serializer = static::getSerializer();

        if ($data instanceof AbstractResponse) {
            $data->prepareSerialization();
        }

        $json = $serializer->serialize($data, 'json', array_merge(array(
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ), $context));

        return new JsonResponse($json, $status, $headers, true);
    }
}
