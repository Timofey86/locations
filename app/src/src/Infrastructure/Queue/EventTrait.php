<?php

namespace App\Infrastructure\Queue;

use App\Domain\Shared\Event\EventInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

trait EventTrait
{
    protected MessageBusInterface $eventBus;

    protected function getEventBus(): MessageBusInterface
    {
        return $this->eventBus;
    }

    protected function event(EventInterface $event): void
    {
        $this->eventBus->dispatch(new Envelope($event))->with(new DispatchAfterCurrentBusStamp());
    }
}
