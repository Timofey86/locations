<?php

namespace App\Infrastructure\Queue\Middleware\DispatchedMessageNameLogger;

interface DispatchedMessageNameLoggerInterface
{
    public function add(string $fqcn): void;

    public function has(string $fqcn): bool;

    public function get(string $fqcn): int;

    public function erase(): void;
}
