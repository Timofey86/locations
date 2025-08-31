<?php

namespace App\Infrastructure\Queue\Middleware;

use App\Infrastructure\Queue\Middleware\DispatchedMessageNameLogger\DispatchedMessageNameLoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class MessageNameLoggingMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly DispatchedMessageNameLoggerInterface $dispatchedMessageNameLogger)
    {
    }

    #[\Override]
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->dispatchedMessageNameLogger->add($envelope->getMessage()::class);

        return $stack->next()->handle($envelope, $stack);
    }
}
