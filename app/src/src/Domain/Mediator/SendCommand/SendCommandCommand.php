<?php

declare(strict_types=1);

namespace App\Domain\Mediator\SendCommand;

use App\Domain\Shared\Command\Command;
use App\Domain\Shared\Command\MediatorAsyncCommandInterface;

final class SendCommandCommand extends Command implements MediatorAsyncCommandInterface
{
    public string $method;
    public array $payload;

    public static function create(SendCommandDto $dto): self
    {
        $command = new self($dto);

        $command->method = $dto->method;
        $command->payload = $dto->payload;

        return $command;
    }
}
