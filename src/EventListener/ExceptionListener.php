<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 4/3/18
 * Time: 8:59 AM
 */

namespace App\EventListener;


use App\Api\Response\ErrorResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Serializer\SerializerInterface;

class ExceptionListener
{
    /**
     * @var SerializerInterface $serializer
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        //catch only api errors
        if (strpos($event->getRequest()->getPathInfo(), "/api") !== 0) {
            return;
        }

        //format error message
        $exception = $event->getException();
        $message = sprintf(
            'An exception occurred: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
        );

        //construct error response
        $errorObj = new ErrorResponse($message);
        $json = $this->serializer->serialize($errorObj, "json");
        $response = new JsonResponse($json, Response::HTTP_INTERNAL_SERVER_ERROR, [], true);

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}