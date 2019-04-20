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
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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

    /**
     * @var bool
     */
    private $isTestEnvironment;

    /**
     * ExceptionListener constructor.
     *
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(SerializerInterface $serializer, LoggerInterface $logger, ParameterBagInterface $parameterBag)
    {
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->isTestEnvironment = $parameterBag->get('APP_ENV') === 'test';
    }

    /**
     * @param GetResponseForExceptionEvent $event
     *
     * @throws Exception
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        //catch only api errors
        if (mb_strpos($event->getRequest()->getPathInfo(), '/api') !== 0 && mb_strpos($event->getRequest()->getPathInfo(), '/external/api') !== 0) {
            return;
        }

        //format error message
        $exception = $event->getException();
        $message = sprintf(
            'An exception occurred: %s with code %s',
            $exception->getMessage(),
            $exception->getCode()
        );

        if ($this->isTestEnvironment) {
            throw $exception;
        } elseif (\function_exists('dump')) {
            dump($exception);
        }

        //construct error response
        $errorObj = new ErrorResponse($message);
        $json = $this->serializer->serialize($errorObj, 'json');
        $response = new JsonResponse($json, Response::HTTP_INTERNAL_SERVER_ERROR, [], true);

        $this->logger->error('api error: ' . $exception->getMessage() . ' at ' . $exception->getFile() . ' line ' . $exception->getLine() . $exception->getTraceAsString() . " for \n" . $event->getRequest()->getContent());

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}
