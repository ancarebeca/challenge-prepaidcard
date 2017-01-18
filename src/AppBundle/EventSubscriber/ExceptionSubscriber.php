<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Exception\DomainException;
use AppBundle\Exception\ParameterNotFoundException;
use AppBundle\Exception\ResourceNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array(
                array('ExceptionHandler'),
            )
        );
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function ExceptionHandler(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $response = new JsonResponse(['error' => $exception->getMessage()]);

        if ($exception instanceof DomainException || $exception instanceof ParameterNotFoundException) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if ($exception instanceof ResourceNotFoundException) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        $event->setResponse($response);
    }
}