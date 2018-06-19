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

class ExceptionListener
{
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
            'An exception occurred. Error says: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
        );

        //construct base response
        $errorObj = new ErrorResponse($message);
        $response = new JsonResponse($errorObj, Response::HTTP_INTERNAL_SERVER_ERROR);

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}