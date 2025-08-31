<?php

namespace App\Infrastructure\Queue;

use App\Domain\Shared\Command\CommandInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

trait BusTrait
{
    protected MessageBusInterface $commandBus;

    protected function getCommandBus(): MessageBusInterface
    {
        return $this->commandBus;
    }

    protected function handle(CommandInterface $command): void
    {
        $this->getCommandBus()->dispatch($command);
    }

    protected function handleAfter(CommandInterface $command): void
    {
        $this->getCommandBus()->dispatch(new Envelope($command))->with(new DispatchAfterCurrentBusStamp());
    }
}
