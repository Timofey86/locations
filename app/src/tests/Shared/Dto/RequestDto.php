<?php

namespace App\Tests\Shared\Dto;

class RequestDto
{
    public string $method;
    public string $uri;
    public array $parameters;
    public array $files;
    public array $server;
    public ?string $content = null;

    public static function create(string $method, string $uri, array $parameters = [], array $files = [], array $server = [], ?string $content = null): RequestDto
    {
        $dto = new self();

        $dto->method = $method;
        $dto->uri = $uri;
        $dto->parameters = $parameters;
        $dto->files = $files;
        $dto->server = $server;
        $dto->content = $content;

        return $dto;
    }
}
