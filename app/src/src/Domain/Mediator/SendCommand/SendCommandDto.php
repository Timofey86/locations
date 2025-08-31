<?php

declare(strict_types=1);

namespace App\Domain\Mediator\SendCommand;

use App\Domain\Shared\Dto\Dto;

final class SendCommandDto extends Dto
{
    public string $method;

    public array $payload = [];

    public static function create(string $method, array $payload): SendCommandDto
    {
        $dto = new SendCommandDto();
        $dto->method = $method;
        $dto->payload = $payload;
        return $dto;
    }
}
