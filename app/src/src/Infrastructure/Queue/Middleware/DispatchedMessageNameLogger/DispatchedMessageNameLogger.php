<?php

namespace App\Infrastructure\Queue\Middleware\DispatchedMessageNameLogger;

class DispatchedMessageNameLogger implements DispatchedMessageNameLoggerInterface
{
    private static $fqcns = [];

    #[\Override]
    public function add(string $fqcn): void
    {
        if ($this->has($fqcn)) {
            ++self::$fqcns[$fqcn];
        } else {
            self::$fqcns[$fqcn] = 1;
        }
    }

    #[\Override]
    public function has(string $fqcn): bool
    {
        return $this->get($fqcn) > 0;
    }

    #[\Override]
    public function get(string $fqcn): int
    {
        return self::$fqcns[$fqcn] ?? 0;
    }

    #[\Override]
    public function erase(): void
    {
        self::$fqcns = [];
    }
}
