<?php

namespace App\Domain\Mediator\SendCommand;

use App\Helper\List\CommandExecutionStatusListHelper;
use InvalidArgumentException;

class SendCommandDtoFactory
{
    public static function create(string $method, array $payload): SendCommandDto
    {
        return SendCommandDto::create($method, $payload);
    }

    public static function createByNotifyCommandExecution(string $visitorId, string $operationId, string $operationStatus, ?array $payload = []): SendCommandDto
    {
        if (!array_key_exists($operationStatus, CommandExecutionStatusListHelper::getList())) {
            throw new InvalidArgumentException("Invalid status: $operationStatus");
        }

        return SendCommandDto::create(
            'frontend_communication.command_execution_status.send',
            [
                '_visitorId' => $visitorId,
                '_operationId' => $operationId,
                'operationStatus' => $operationStatus,
                'payload' => json_encode($payload, JSON_FORCE_OBJECT)
            ]
        );
    }
}
