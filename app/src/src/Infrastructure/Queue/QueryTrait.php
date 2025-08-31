<?php

namespace App\Infrastructure\Queue;

use App\Domain\Shared\Query\Query;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

trait QueryTrait
{
    protected MessageBusInterface $queryBus;

    protected function getQueryBus(): MessageBusInterface
    {
        return $this->queryBus;
    }

    protected function query(Query $query): array
    {
        $envelope = $this->getQueryBus()->dispatch($query);

        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp->getResult();
    }
}
