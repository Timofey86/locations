<?php

namespace App\Tests\Shared\Dto;

class OpenApiTestDto
{
    public RequestDto $request;
    public array $validators;
    public int $expectedStatusCode;

    public static function create(RequestDto $request, array $validators = [], int $expectedStatusCode = 200): OpenApiTestDto
    {
        $dto = new self();

        $dto->request = $request;
        $dto->validators = $validators;
        $dto->expectedStatusCode = $expectedStatusCode;

        return $dto;
    }
}
