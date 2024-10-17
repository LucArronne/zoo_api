<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ValidationFailedException) {
            $violations = $exception->getViolations();
            $errors = [];

            foreach ($violations as $violation) {
                $errors[] = [
                    "property" => $violation->getPropertyPath(),
                    "detail" => $violation->getMessage(),
                ];
            }
            $event->setResponse(new JsonResponse(
                [
                    "status" => Response::HTTP_BAD_REQUEST,
                    "message" => "Data validation failed",
                    'errors' => $errors,
                ],
                Response::HTTP_BAD_REQUEST,
            ));
            return;
        }

        if ($exception instanceof HttpException) {
            $data = [
                "status" => $exception->getStatusCode(),
                "message" => $exception->getMessage(),

            ];
            $event->setResponse(new JsonResponse($data));

        } else {
            $data = [
                "status" => Response::HTTP_INTERNAL_SERVER_ERROR,
                "message" => $exception->getMessage(),
            ];
            $event->setResponse(new JsonResponse($data));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
