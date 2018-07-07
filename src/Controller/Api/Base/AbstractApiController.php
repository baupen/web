<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api\Base;

use App\Api\Response\FailResponse;
use App\Api\Response\SuccessfulResponse;
use App\Controller\Base\BaseDoctrineController;
use App\Service\Interfaces\ImageServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractApiController extends BaseDoctrineController
{
    const EMPTY_REQUEST = 'request empty';
    const REQUEST_VALIDATION_FAILED = 'request validation failed, not all required fields are set';

    const EMPTY_REQUEST_STATUS_CODE = 1;
    const REQUEST_VALIDATION_FAILED_STATUS_CODE = 1;
    const UNKNOWN_STATUS_CODE = 1;

    /**
     * gives the appropriate error code the specified error message.
     *
     * @param string $message
     *
     * @return int
     */
    protected function errorMessageToStatusCode($message)
    {
        switch ($message) {
            case self::EMPTY_REQUEST:
                return self::EMPTY_REQUEST_STATUS_CODE;
            case self::REQUEST_VALIDATION_FAILED:
                return self::REQUEST_VALIDATION_FAILED_STATUS_CODE;
            default:
                return self::UNKNOWN_STATUS_CODE;
        }
    }

    /**
     * @param Request $request
     * @param string $targetClass
     * @param mixed|null $parsedRequest
     * @param Response|null $errorResponse
     *
     * @return bool
     */
    protected function parseRequest(Request $request, $targetClass, &$parsedRequest, &$errorResponse)
    {
        //check if empty request, handle multipart correctly
        $content = $request->request->get('message');
        if ($content === null) {
            $content = $request->getContent();
        }
        if (!($content)) {
            $errorResponse = $this->fail(self::EMPTY_REQUEST);

            return false;
        }

        $parsedRequest = $this->get('serializer')->deserialize($content, $targetClass, 'json');

        // check all properties defined
        $errors = $this->get('validator')->validate($parsedRequest);
        if (count($errors) > 0) {
            $errorResponse = $this->fail(self::REQUEST_VALIDATION_FAILED);

            return false;
        }

        return true;
    }

    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * @final
     *
     * @param $data
     * @param int $status
     * @param array $headers
     * @param array $context
     *
     * @return JsonResponse
     */
    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        $serializer = $this->get('serializer');

        $json = $serializer->serialize($data, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS | JSON_UNESCAPED_UNICODE,
        ], $context));

        return new JsonResponse($json, $status, $headers, true);
    }

    /**
     * inject the needed services.
     *
     * @return array
     */
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + [
                'logger' => LoggerInterface::class,
                'serializer' => SerializerInterface::class,
                'validator' => ValidatorInterface::class,
                'request_stack' => RequestStack::class,
                ImageServiceInterface::class => ImageServiceInterface::class,
            ];
    }

    /**
     * if request failed (client error).
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function fail(string $message)
    {
        $this->get('logger')->error('Api fail ' . ': ' . $message . ' for ' . $this->get('request_stack')->getCurrentRequest()->getContent());

        return $this->json(new FailResponse($message, $this->errorMessageToStatusCode($message)), Response::HTTP_BAD_REQUEST);
    }

    /**
     * if request was successful.
     *
     * @param $data
     *
     * @return JsonResponse
     */
    protected function success($data)
    {
        return $this->json(new SuccessfulResponse($data));
    }

    /**
     * @param UploadedFile $file
     * @param $targetFilePath
     * @param $error
     *
     * @return bool|JsonResponse
     */
    protected function uploadImage(UploadedFile $file, $targetFilePath, $error)
    {
        /** @var UploadedFile $file */
        $targetFolder = $this->getParameter('PUBLIC_DIR') . '/' . dirname($targetFilePath);
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }
        if (!$file->move($targetFolder, $targetFilePath)) {
            return $this->fail($error);
        }
        $this->get(ImageServiceInterface::class)->generateThumbnails($this->getParameter('PUBLIC_DIR') . '/' . $targetFilePath);

        return true;
    }
}
