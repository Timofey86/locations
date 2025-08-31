<?php

namespace App\EventSubscriber;

use App\Builder\ExceptionResponseBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubcriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ExceptionResponseBuilder $exceptionResponseBuilder,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException'],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = $this->exceptionResponseBuilder->getExceptionResponse($exception);
        if ($response) {
            $event->setResponse($response);
        }
    }
}
