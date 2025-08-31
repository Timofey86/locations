<?php

namespace App\Infrastructure\Queue\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;

class TotallyFailedMiddleware implements MiddlewareInterface
{
    #[\Override]
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null !== $envelope->last(SentToFailureTransportStamp::class)) {
            $receivedStamps = $envelope->all(ReceivedStamp::class);

            if (count($receivedStamps) && $receivedStamps[0]->getTransportName() == 'failed') {
                $envelope = $envelope->withoutAll(SentToFailureTransportStamp::class);
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
