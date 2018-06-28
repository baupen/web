<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api\External\Base;

use App\Api\Response\FailResponse;
use App\Api\Response\SuccessfulResponse;
use App\Controller\Api\Base\AbstractApiController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class ExternalApiController extends AbstractApiController
{
    const EMPTY_REQUEST = 'request empty';
    const REQUEST_VALIDATION_FAILED = 'request validation failed, not all required fields are set';

    const UNKNOWN_USERNAME = 'unknown username';
    const WRONG_PASSWORD = 'wrong password';
    const AUTHENTICATION_TOKEN_INVALID = 'authentication token invalid';

    const ISSUE_GUID_ALREADY_IN_USE = 'guid already in use';

    const ISSUE_NOT_FOUND = 'issue was not found';
    const ISSUE_ACCESS_DENIED = 'issue access not allowed';
    const ISSUE_ACTION_NOT_ALLOWED = 'this action can not be executed on the entity';

    const MAP_NOT_FOUND = 'map was not found';
    const MAP_ACCESS_DENIED = 'map access not allowed';

    const CRAFTSMAN_NOT_FOUND = 'craftsman was not found';
    const CRAFTSMAN_ACCESS_DENIED = 'craftsman access not allowed';

    const MAP_CRAFTSMAN_NOT_ON_SAME_CONSTRUCTION_SITE = 'the craftsman does not work on the same construction site as the assigned map';

    const ENTITY_NOT_FOUND = 'entity was not found';
    const ENTITY_ACCESS_DENIED = 'you are not allowed to access this entity';
    const ENTITY_NO_DOWNLOADABLE_FILE = 'entity has no file to download';
    const ENTITY_FILE_NOT_FOUND = 'the server could not find the file of the entity';

    const ISSUE_FILE_UPLOAD_FAILED = 'the uploaded file could not be processes';
    const ISSUE_NO_FILE_TO_UPLOAD = 'no file could be found in the request, but one was expected';
    const ISSUE_NO_FILE_UPLOAD_EXPECTED = 'a file was uploaded, but not specified in the issue';
    const INVALID_TIMESTAMP = 'invalid timestamp';

    private function errorMessageToStatusCode($message)
    {
        switch ($message) {
            case static::EMPTY_REQUEST:
            case static::REQUEST_VALIDATION_FAILED:
            case static::ISSUE_FILE_UPLOAD_FAILED:
                return 1;
            case static::AUTHENTICATION_TOKEN_INVALID:
                return 2;
            case static::UNKNOWN_USERNAME:
                return 100;
            case static::WRONG_PASSWORD:
                return 101;
            case static::ISSUE_GUID_ALREADY_IN_USE:
                return 200;
            case static::ISSUE_NOT_FOUND:
                return 201;
            case static::ISSUE_ACTION_NOT_ALLOWED:
                return 203;
        }

        return 202;
    }

    /**
     * inject the translator service.
     *
     * @return array
     */
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + ['translator' => TranslatorInterface::class, 'logger' => LoggerInterface::class];
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
        $logger = $this->get('logger');
        $request = $this->get('request_stack')->getCurrentRequest();
        $logger->error('Api fail ' . ': ' . $message . ' for ' . $request->getContent());

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
}
