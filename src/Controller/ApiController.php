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


use App\Api\Request\LoginRequest;
use App\Api\Response\Data\LoginData;
use App\Api\Response\FailResponse;
use App\Api\Response\SuccessfulResponse;
use App\Controller\Base\BaseDoctrineController;
use App\Entity\AuthenticationToken;
use App\Entity\ConstructionManager;
use App\Service\Interfaces\ApiEntityConversionServiceInterface;
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
    const EMPTY_REQUEST = "request empty";
    const UNKNOWN_USERNAME = "unknown username";
    const WRONG_PASSWORD = "wrong password";

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
     * @param ApiEntityConversionServiceInterface $apiEntityConversionService
     * @return Response
     */
    public function loginAction(Request $request, SerializerInterface $serializer, ApiEntityConversionServiceInterface $apiEntityConversionService)
    {
        if (!($content = $request->getContent())) {
            return $this->json(new FailResponse(static::EMPTY_REQUEST));
        }

        /* @var LoginRequest $loginRequest */
        $loginRequest = $serializer->deserialize($content, LoginRequest::class, "json");

        $constructionManager = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(["email" => $loginRequest->getUsername()]);
        if ($constructionManager === null) {
            return $this->json(new FailResponse(static::UNKNOWN_USERNAME));
        }
        if ($constructionManager->getPasswordHash() !== $loginRequest->getPasswordHash()) {
            return $this->json(new FailResponse(static::WRONG_PASSWORD));
        }

        $authToken = new AuthenticationToken($constructionManager);
        $this->fastSave($authToken);

        $user = $apiEntityConversionService->convertToUser($constructionManager, $authToken->getToken());

        $loginResponse = new LoginData($user);
        return $this->json(new SuccessfulResponse($loginResponse));
    }


    /**
     * @Route("/file/upload", name="api_file_upload")
     *
     * @param Request $request
     * @return Response
     */
//    public function fileUploadAction(Request $request)
//    {
//        foreach ($request->files->all() as $key => $file) {
//            /** @var UploadedFile $file */
//            if (!$file->move($this->getParameter("PUBLIC_DIR") . "/upload", $file->getClientOriginalName())) {
//                return $this->failed(ApiStatus::INVALID_FILE);
//            }
//        }
//
//        return $this->file($this->getParameter("PUBLIC_DIR") . "/upload/" . $downloadFileRequest->getFileName());
//    }

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
        $serializer = new Serializer([new DateTimeNormalizer(), new ObjectNormalizer()], [new JsonEncoder()]);

        $json = $serializer->serialize($data, 'json', array_merge(array(
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ), $context));

        return new JsonResponse($json, $status, $headers, true);
    }
}
