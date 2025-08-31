<?php

namespace App\Infrastructure\Queue;

use App\Domain\Shared\Command\CommandInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

trait MediatorBusTrait
{
    protected MessageBusInterface $mediatorCommandBus;

    protected function getMediatorCommandBus(): MessageBusInterface
    {
        return $this->mediatorCommandBus;
    }

    protected function mediatorHandle(CommandInterface $command): void
    {
        $this->getMediatorCommandBus()->dispatch($command);
    }

    protected function mediatorHandleAfter(CommandInterface $command): void
    {
        $this->getMediatorCommandBus()->dispatch(new Envelope($command))->with(new DispatchAfterCurrentBusStamp());
    }
}
