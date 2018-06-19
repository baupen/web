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


use App\Api\Request\IssueModify;
use App\Api\Request\LoginRequest;
use App\Api\Response\Data\EmptyData;
use App\Api\Response\Data\LoginData;
use App\Api\Response\FailResponse;
use App\Api\Response\SuccessfulResponse;
use App\Controller\Base\BaseDoctrineController;
use App\Entity\AuthenticationToken;
use App\Entity\ConstructionManager;
use App\Entity\Issue;
use App\Service\Interfaces\ApiEntityConversionServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 *
 * @return Response
 */
class ApiController extends BaseDoctrineController
{
    const EMPTY_REQUEST = "request empty";
    const INVALID_REQUEST = "invalid request";
    const UNKNOWN_USERNAME = "unknown username";
    const WRONG_PASSWORD = "wrong password";
    const GUID_ALREADY_IN_USE = "guid already in use";
    const AUTHENTICATION_TOKEN_INVALID = "authentication token invalid";

    /**
     * inject the translator service
     *
     * @return array
     */
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + ['translator' => TranslatorInterface::class, "logger" => LoggerInterface::class];
    }

    /**
     * @Route("/login", name="api_login")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param ApiEntityConversionServiceInterface $apiEntityConversionService
     * @return Response
     */
    public function loginAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, ApiEntityConversionServiceInterface $apiEntityConversionService)
    {
        //check if empty request
        if (!($content = $request->getContent())) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var LoginRequest $loginRequest */
        $loginRequest = $serializer->deserialize($content, LoginRequest::class, "json");

        // check all properties defined
        $errors = $validator->validate($loginRequest);
        if (count($errors) > 0) {
            return $this->fail(static::INVALID_REQUEST);
        }

        //check username & password
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(ConstructionManager::class)->findOneBy(["email" => $loginRequest->getUsername()]);
        if ($constructionManager === null) {
            return $this->fail(static::UNKNOWN_USERNAME);
        }
        if ($constructionManager->getPasswordHash() !== $loginRequest->getPasswordHash()) {
            return $this->fail(static::WRONG_PASSWORD);
        }

        //create auth token
        $authToken = new AuthenticationToken($constructionManager);
        $this->fastSave($authToken);

        //construct answer
        $user = $apiEntityConversionService->convertToUser($constructionManager, $authToken->getToken());
        $loginData = new LoginData($user);
        return $this->success($loginData);
    }

    /**
     * @Route("/issue/create", name="issue_create")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param ApiEntityConversionServiceInterface $apiEntityConversionService
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function issueCreateAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, ApiEntityConversionServiceInterface $apiEntityConversionService)
    {
        //check if empty request
        if (!($content = $request->getContent())) {
            return $this->fail(static::EMPTY_REQUEST);
        }

        /* @var IssueModify $issueModifyRequest */
        $issueModifyRequest = $serializer->deserialize($content, IssueModify::class, "json");

        // check all properties defined
        $errors = $validator->validate($issueModifyRequest);
        if (count($errors) > 0) {
            return $this->fail(static::INVALID_REQUEST);
        }

        //check auth token
        /** @var ConstructionManager $constructionManager */
        $constructionManager = $this->getDoctrine()->getRepository(AuthenticationToken::class)->getConstructionManager($issueModifyRequest);
        if ($constructionManager === null) {
            return $this->fail(static::AUTHENTICATION_TOKEN_INVALID);
        }

        $issue =        $apiEntityConversionService->getIssue();

        //construct answer
        return $this->success(new EmptyData());
    }


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
     * if request failed
     *
     * @param string $message
     * @param int $code
     * @return Response
     */
    protected function fail(string $message)
    {
        $logger = $this->get("logger");
        $request = $this->get("request_stack")->getCurrentRequest();
        $logger->error("Api fail " . ": " . $message . " for " . $request->getContent());
        $code = Response::HTTP_OK;
        switch ($message) {
            case static::INVALID_REQUEST:
                $code = Response::HTTP_BAD_REQUEST;
                break;
            case static::AUTHENTICATION_TOKEN_INVALID:
                $code = Response::HTTP_UNAUTHORIZED;
                break;
        }
        return $this->json(new FailResponse($message), $code);
    }

    /**
     * if request was successful
     *
     * @param $data
     * @return JsonResponse
     */
    protected function success($data)
    {
        return $this->json(new SuccessfulResponse($data));
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
        $serializer = $this->get("serializer");

        $json = $serializer->serialize($data, 'json', array_merge(array(
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ), $context));

        return new JsonResponse($json, $status, $headers, true);
    }
}
