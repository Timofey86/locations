<?php

namespace App\Domain\Shared\Notifier;

interface NotifierInterface
{
    public function notify(string $message): void;
}