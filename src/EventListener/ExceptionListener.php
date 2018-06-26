<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventListener;

use App\Api\Response\ErrorResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Serializer\SerializerInterface;

class ExceptionListener
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        //catch only api errors
        if (0 !== mb_strpos($event->getRequest()->getPathInfo(), '/api')) {
            return;
        }

        //format error message
        $exception = $event->getException();
        $message = sprintf(
            'An exception occurred: %s with code %s',
            $exception->getMessage(),
            $exception->getCode()
        );

        //construct error response
        $errorObj = new ErrorResponse($message, 300);
        $json = $this->serializer->serialize($errorObj, 'json');
        $response = new JsonResponse($json, Response::HTTP_INTERNAL_SERVER_ERROR, [], true);

        $this->logger->error('api error: ' . $exception->getMessage() . ' at ' . $exception->getFile() . ' line ' . $exception->getLine() . $exception->getTraceAsString() . " for \n" . $event->getRequest()->getContent());

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}
