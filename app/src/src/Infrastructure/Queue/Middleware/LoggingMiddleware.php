<?php

namespace App\Infrastructure\Queue\Middleware;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Throwable;

class LoggingMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws Throwable
     * @throws ExceptionInterface
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            $message = $envelope->getMessage();
            $envelope = $stack->next()->handle($envelope, $stack);
        } catch (Throwable $throwable) {

            $this->logger->critical(
                'Dto: ' . $message->getDto()->toJson()
            );
            throw $throwable;
        }
        return $envelope;
    }
}
