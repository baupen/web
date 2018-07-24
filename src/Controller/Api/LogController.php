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

use App\Api\Request\Log\ErrorRequest;
use App\Api\Response\Data\EmptyData;
use App\Controller\Api\Base\ApiController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/log")
 */
class LogController extends ApiController
{
    /**
     * @Route("/error", name="api_log_error", methods={"POST"})
     *
     * @param Request $request
     * @param LoggerInterface $logger
     *
     * @return Response
     */
    public function errorAction(Request $request, LoggerInterface $logger)
    {
        /** @var ErrorRequest $parsedRequest */
        if (parent::parseRequest($request, ErrorRequest::class, $parsedRequest, $errorResponse)) {
            $logger->error($parsedRequest->getMessage());
        }

        return $this->success(new EmptyData());
    }
}
