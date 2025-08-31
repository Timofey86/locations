<?php

namespace App\Domain\Mediator\Service;

use App\Domain\Mediator\SendCommand\SendCommandCommand;
use App\Domain\Mediator\SendCommand\SendCommandDtoFactory;
use App\Helper\List\CommandExecutionStatusListHelper;
use App\Infrastructure\Queue\MediatorBusTrait;
use http\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\MessageBusInterface;

class NotifyCommandExecutionService
{
    use MediatorBusTrait;

    public function __construct(
        protected MessageBusInterface $mediatorCommandBus
    ) {
    }

    public function __invoke(string $visitorId, string $operationId, string $status, ?array $payload = []): void
    {
        if (!array_key_exists($status, CommandExecutionStatusListHelper::getList())) {
            throw new InvalidArgumentException("Invalid status: $status");
        }

        $this->mediatorHandle(SendCommandCommand::create(SendCommandDtoFactory::createByNotifyCommandExecution($visitorId, $operationId, $status, $payload)));
    }
}
